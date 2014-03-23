<?php

class Cfe_Acl_Exception extends Exception {
    const NO_ROLES_DEFINED = 1;
    const UNABLE_TO_CONNECT_LDAP_SERVER = 2;
    const UNABLE_TO_PERFORM_LDAP_SEARCH = 3;
    const LDAP_DOMAIN_DOESN_T_EXIST = 4;

    const AUTH_ERROR_NO_HEADERS   = 5;
    const AUTH_ERROR_NOT_BASIC    = 6;
    const AUTH_ERROR_INVALID_AUTH = 7;
}
