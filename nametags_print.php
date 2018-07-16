<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Attendance report
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
$pageparams = new mod_attendance_report_page_params();
$pageparams->sort = optional_param('sort', ATT_SORT_FIRSTNAME, PARAM_INT);
$pageparams->perpage = 2000;
$id                 = required_param('id', PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$cm        = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course    = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$attrecord = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
require_login($course, true, $cm);
// Print an individual nametag.
if ($userid) {
    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
    $userfullname = $user->firstname . ' ' . $user->lastname;
    // Calculate file name.
    $filename = str_replace(' ', '_',
            clean_filename(
                $course->shortname . ' ' .
                get_string('modulename', 'attendance') . ' ' .
                strip_tags(format_string($userfullname, true))
            )) . '.pdf';
    $pdf = attendance_create_nametags_pdf_object($userfullname, $course->shortname);
    attendance_create_user_nametag($user, $course, $pdf);
    $pdf->Output($filename, 'D');
    exit();
}
// Print all users nametags.
$context = context_module::instance($cm->id);
$att = new mod_attendance_structure($attrecord, $cm, $course, $context, $pageparams);
$reportdata = new attendance_nametags_data($att);
// Calculate file name.
$filename = str_replace(' ', '_',
    clean_filename(
        $course->shortname . ' ' .
        get_string('modulenameplural', 'attendance') . ' ' .
        strip_tags(format_string($attrecord->name, true))
    )) . '.pdf';
$pdf = attendance_create_nametags_pdf_object($attrecord->name, $course->shortname);
foreach ($reportdata->users as $user) {
    attendance_create_user_nametag($user, $course, $pdf);
}
$pdf->Output($filename, 'D');
exit();