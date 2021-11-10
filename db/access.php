<?php

/**
 * Course activities block caps.
 *
 * @package    block_course_activities
 */
defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'block/course_activities:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
);
