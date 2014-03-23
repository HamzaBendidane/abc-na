<?php

class Cfe_Acl {
    const CACHE_TTL = 3600; // 1 Hour

    protected $domain;
    protected $user = null;

    public function __construct($domain = null) {
        if(is_null($domain)) {
            $this->domain = $this->getDomain();
        } else {
            $this->domain = $domain;
        }
    }

    protected function getDomain() {
        $vhost = $_SERVER['SERVER_NAME'];
        if(!preg_match('~(?:0pb\.org|4rnd\.com)$~', $vhost)) {
            return $vhost;
        } elseif(Cfe_Utils::getPlatform() == 'prod') {
            return substr($vhost, 0, -4);
        } else {
            $host_array = array_reverse(explode('.', $vhost));
            unset($host_array[0]);
            unset($host_array[2]);
            return implode('.', array_reverse($host_array));
        }
    }

    protected function getUserRoles($user) {
        if(function_exists('apc_fetch')){
            $key = __CLASS__.'::'.__FUNCTION__."($user)";
            if(false !== ($roles = apc_fetch($key))) {
                return $roles;
            }
        }
        if(!$ldapconn = ldap_connect("ldap")) {
            throw new Cfe_Acl_Exception('unable to conect to LDAP server', Cfe_Acl_Exception::UNABLE_TO_CONNECT_LDAP_SERVER);
        }
        if(!$sr=@ldap_search($ldapconn, "ou=$this->domain,ou=applications,dc=4rnd,dc=com", "(member=uid=$user,ou=users,dc=4rnd,dc=com)", array('dn'),0,50,1)) {
            switch(ldap_errno($ldapconn)) {
                case 32: // NO SUCH OBJECT
                    throw new Cfe_Acl_Exception("domain '$this->domain' doesn\'t exist in LDAP", Cfe_Acl_Exception::LDAP_DOMAIN_DOESN_T_EXIST);
                    break;
                default:
                    throw new Cfe_Acl_Exception('unable to complete the LDAP search : ['.ldap_errno($ldapconn).'] '.ldap_error($ldapconn), Cfe_Acl_Exception::UNABLE_TO_PERFORM_LDAP_SEARCH);
            }
        }
        $info = ldap_get_entries($ldapconn, $sr);
        $roles = array();
        for($i=0; $i < $info['count']; $i++) {
            $dn = $info[$i]['dn'];
            $roles[] = substr($dn,3,strpos($dn, ',')-3);

        }
        if(function_exists('apc_store')){
            apc_store($key, $roles, self::CACHE_TTL);
        }
        return $roles;
    }

    /**
     *
     * check if the user has the right autorization on ldap to use this class
     * i.e. check if the user is member of a group in that domain which common name is the same as one of the roles listed in the class PHPDoc @roles tag.
     * be carrefull it doesn't check for the inherited classes.
     *
     * @param string $user
     * @param string|object $class
     * @return boolean
     */
    public function isAuthorizedOnClass($user, $class) {
        $userRoles = $this->getUserRoles($user);
        $classRoles = $this->getClassRoles(new ReflectionClass($class));
        return count(array_intersect($classRoles, $userRoles)) > 0;
    }

    /**
     *
     * check if the user has any of the roles provided
     * i.e. check if the user is member of a group in that domain which common name is the same as one of the roles provided.
     *
     * @param string $user
     * @param array $roles
     * @return boolean
     */
    public function isAuthorizedOnRoles($user, array $roles) {
        $userRoles = $this->getUserRoles($user);
        return count(array_intersect($roles, $userRoles)) > 0;
    }

    protected function getClassRoles($reflexionClass) {
        if(($dc = $reflexionClass->getDocComment())
        && preg_match('/\v\h*\*\h+\@roles\h+(\V*)?/', $dc, $matches)) {
            return array_map('trim', explode(',',$matches[1]));
        } elseif($rpc = $reflexionClass->getParentClass()) {
            return $this->getClassRoles($rpc);
        } else {
            throw new Cfe_Acl_Exception('no @roles tag found in the class '.$reflexionClass->getName(), Cfe_Acl_Exception::NO_ROLES_DEFINED);
        }
    }

    public function getUser() {
        if(is_null($this->user)) {
            $headers = apache_request_headers();
            if(!is_array($headers) || !array_key_exists('Authorization', $headers) || (6 > strlen(($auth = $headers['Authorization'])))) {
                throw new Cfe_Acl_Exception('no Authorization header', Cfe_Acl_Exception::AUTH_ERROR_NO_HEADERS);
            } elseif(strncmp($auth = trim($auth), 'Basic ',6) !== 0) {
                throw new Cfe_Acl_Exception('not Basic Authorization', Cfe_Acl_Exception::AUTH_ERROR_NOT_BASIC);
            } elseif(!($auth = base64_decode(substr($auth,6))) || (count($list = explode(':',$auth)) !== 2)) {
                throw new Cfe_Acl_Exception('invalid Authorization string decoded', Cfe_Acl_Exception::AUTH_ERROR_INVALID_AUTH);
            } else {
                $this->user = $list[0];
            }
        }
        return $this->user;
    }
}
