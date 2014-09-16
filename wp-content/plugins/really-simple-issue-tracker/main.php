<?php
/**
 Plugin Name: Really Simple Issue Tracker
 Description: Let's you setup and keep track of simple tasks or issues to be done, connect them with a project and optionally list them publicly in a widget.
 Author: Andreas Lundgren
 Author e-mail: aaslun04@gmail.com
 Version: 1.0
 */

require_once 'class-really-simple-issue-tracker.php';
require_once 'class-issue-type.php';
require_once 'class-status.php';
require_once 'widgets/widget-issue-list.php';
require_once 'hooks.php';
$tracker = new ReallySimpleIssueTracker();
