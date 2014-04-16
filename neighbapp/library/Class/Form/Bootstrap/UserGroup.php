<?php

/**
 * Form user_group Home
 * @author Aris Dieudop <aris@surikate.com>
 */
class Class_Form_Bootstrap_UserGroup extends Twitter_Bootstrap_Form_Horizontal {

    const NAME = 'user_groups';

    
    
    public function init() {
        // $this->setElementsBelongTo('data');
               
        $list = Cfe_Numbate_Critarea::$list_CritareaTypeGroup;
        
        $this->getElement('type')->setOptions(array(
            'belongsTo' => 'group'
        ))->addMultiOptions($list);
        
        $this->getElement('name')->setOptions(array(
            'belongsTo' => 'group'
        ));

        $this->getElement('description')->setOptions(array(
            'belongsTo' => 'group'
        ));

        $this->getElement('status')->setOptions(array(
            'belongsTo' => 'group'
        ));

        $this->getElement('user')->setOptions(array(
            'belongsTo' => 'group_rights'
        ));

        $this->getElement('company')->setOptions(array(
            'belongsTo' => 'group_rights'
        ));

        $this->getElement('reporting')->setOptions(array(
            'belongsTo' => 'group_rights'
        ));

        $this->getElement('group_right')->setOptions(array(
            'belongsTo' => 'group_rights'
        ));

        $this->getElement('graph')->setOptions(array(
            'belongsTo' => 'group_rights'
        ));

        $groups = Cfe_Numbate_Rights::$Label;

        $elements_to_display_in_right_group = array();
        
        foreach ($groups as $group) {
            foreach ($group as $key => $value) {

                $elements_to_display_in_right_group[] = $key;

                $this->addElement('checkbox', (string) $key, array(
                    'label' => $value['name'],
                    'belongsTo' => 'rights'
                ));
            }
        }

        $reportingFields = Cfe_Numbate_Rights::$reportingFieldLabel;

        $elements_to_display_in_field_group = array();

        foreach ($reportingFields as $key => $value) {

            $elements_to_display_in_field_group[] = $key;

            $this->addElement('checkbox', (string) $key, array(
                'label' => $value,
                'belongsTo' => 'field'
            ));
        }

        $reportingSelect = Cfe_Numbate_Rights::$reportingSelectLabel;

        $elements_to_display_in_select_group = array();

        foreach ($reportingSelect as $key => $value) {

            $elements_to_display_in_select_group[] = $key;

            $this->addElement('checkbox', (string) $key, array(
                'label' => $value,
                'belongsTo' => 'select'
            ));
        }

        $graphs = Cfe_Numbate_Rights::$graphLabel;

        $elements_to_display_in_graph_group = array();

        foreach ($graphs as $key => $value) {

            $elements_to_display_in_graph_group[] = $key;
            $this->addElement('checkbox', (string) $key, array(
                'label' => $value,
                'belongsTo' => 'group_graph'
            ));
        }

        
        $this->addDisplayGroup(
            array('name', 'description','type', 'status'), 'group', array(
            'legend' => 'Admin of Groups',
            'class' => 'col-sm-12',
            'data-index' => "group_static"
            )
        );

        $this->addDisplayGroup(
            array('user','company','reporting','group_right','graph'), 'group_rights', array(
            'legend' => 'Group of Actions',
            'class' => 'col-sm-6',
            'data-index' => "group_static"
            )
        );

        $this->addDisplayGroup(
                $elements_to_display_in_right_group, 'rigth', array(
            'legend' => 'Admin of Rights',
            'class' => 'col-sm-6',
            'data-index' => "group_right"
            )
        );

        $this->addDisplayGroup(
                $elements_to_display_in_select_group, 'select', array(
            'legend' => 'Reporting Select',
            'class' => 'col-sm-6',
            'data-index' => "group_select"
            )
        );

        $this->addDisplayGroup(
                $elements_to_display_in_field_group, 'field', array(
            'legend' => 'Reporting Fields',
            'class' => 'col-sm-6',
            'data-index' => "group_field"
            )
        );

        $this->addDisplayGroup(
                $elements_to_display_in_graph_group, 'group_graph', array(
            'legend' => 'Graph',
            'class' => 'col-sm-6',
            'data-index' => "group_graph"
            )
        );

        $this->addElement('submit', "submit", array(
            'class' => 'btn'
        ));
    }

}
