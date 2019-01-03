<?php

namespace SmartStructure {

    class Task extends \Anakeen\SmartStructures\Task\TaskBehavior
    {
        const familyName = "TASK";
    }
}

namespace SmartStructure\Fields {

    class Task
    {
        /**
        * Identification
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const task_fr_ident='task_fr_ident';
        /**
        * Title
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>is-title</i> true </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const task_title='task_title';
        /**
        * Executed by
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>match</i> user </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> account </li>
        * </ul>
        */ 
        const task_iduser='task_iduser';
        /**
        * Status
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>relation</i> TASK-task_status </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const task_status='task_status';
        /**
        * Description
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const task_desc='task_desc';
        /**
        * Route to execute
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const task_fr_route='task_fr_route';
        /**
        * Namespace
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const task_route_ns='task_route_ns';
        /**
        * Name
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const task_route_name='task_route_name';
        /**
        * Method
        * <ul>
        * <li> <i>relation</i> TASK-task_method </li>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>needed</i> true </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const task_route_method='task_route_method';
        /**
        * Arguments
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const task_t_args='task_t_args';
        /**
        * Nom
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const task_arg_name='task_arg_name';
        /**
        * Valeur
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const task_arg_value='task_arg_value';
        /**
        * Query fields
        * <ul>
        * <li> <i>type</i> array </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const task_t_queryfield='task_t_queryfield';
        /**
        * Nom
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const task_queryfield_name='task_queryfield_name';
        /**
        * Valeur
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const task_queryfield_value='task_queryfield_value';
        /**
        * Schedule
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> ReadWrite </li>
        * </ul>
        */ 
        const task_fr_schedule='task_fr_schedule';
        /**
        * Crontab periodicity
        * <ul>
        * <li> <i>access</i> ReadWrite </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const task_crontab='task_crontab';
        /**
        * Periodicity (comprehensive)
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> text </li>
        * </ul>
        */ 
        const task_humancrontab='task_humancrontab';
        /**
        * Next execution date
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>is-abstract</i> true </li>
        * <li> <i>type</i> timestamp </li>
        * </ul>
        */ 
        const task_nextdate='task_nextdate';
        /**
        * Task results
        * <ul>
        * <li> <i>type</i> frame </li>
        * <li> <i>access</i> Read </li>
        * </ul>
        */ 
        const task_fr_result='task_fr_result';
        /**
        * Execution date
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> timestamp </li>
        * </ul>
        */ 
        const task_exec_date='task_exec_date';
        /**
        * Execution duration
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> time </li>
        * </ul>
        */ 
        const task_exec_duration='task_exec_duration';
        /**
        * Execution status
        * <ul>
        * <li> <i>relation</i> TASK-task_result-status </li>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> enum </li>
        * </ul>
        */ 
        const task_exec_state_result='task_exec_state_result';
        /**
        * Output
        * <ul>
        * <li> <i>access</i> Read </li>
        * <li> <i>type</i> longtext </li>
        * </ul>
        */ 
        const task_exec_output='task_exec_output';

    }
}