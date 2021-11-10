<?php

/**
 * Activities.
 *
 * @package    block_course_activities
 */
defined('MOODLE_INTERNAL') || die();

class block_course_activities extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_course_activities');
    }

    function has_config() {
        return false;
    }

    /**
     * Core function, specifies where the block can be used.
     * @return array
     */
    public function applicable_formats() {
        return ['course' => true];
    }

    public function get_content() {
        global $CFG, $USER, $OUTPUT;

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $course = $this->page->course;

        $completion_info = new completion_info($course);

        if (!$completion_info->is_enabled()) {
            $this->content->text .= get_string('completionnotenabledforcourse', 'completion');
            return $this->content;
        }

        $completions = $completion_info->get_completions($USER->id);

        // Check if this course has any criteria.
        if (empty($completions)) {
            $this->content->text .= get_string('nocriteriaset', 'completion');
            return $this->content;
        }

        // Check this user is enroled.
        if ($completion_info->is_tracked_user($USER->id)) {

            // For aggregating activity completion.
            $activities = [];

            // Loop through course criteria.
            foreach ($completions as $completion) {
                $criteria = $completion->get_criteria();
                $complete = $completion->is_complete();

                if ($criteria->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {
                    $activities[$criteria->moduleinstance] = [get_coursemodule_from_id($criteria->module, $criteria->moduleinstance), $complete];
                }
            }

            $activities_rows = [];

            // Aggregate activities.
            if (!empty($activities)) {
                foreach ($activities as $cmid => $data) {
                    $modfullname = '';
                    if ($data[0]->modname === 'resources') {
                        continue;
                    } else {
                        $icon = $OUTPUT->image_icon('icon', get_string('pluginname', $data[0]->modname), $data[0]->modname);
                        $modfullname = html_writer::link(new moodle_url('/mod/' . $data[0]->modname . '/view.php?id=' . $cmid), $icon . $data[0]->name);
                    }
                    $row = new html_table_row();
                    $row->cells[0] = new html_table_cell($cmid);
                    $row->cells[1] = new html_table_cell($modfullname);
                    $row->cells[2] = new html_table_cell(date('d-M-Y', $data[0]->added));
                    $row->cells[3] = new html_table_cell($data[1] ? get_string('completed', 'block_course_activities')
                        : get_string('pending', 'block_course_activities'));
                    $activities_rows[] = $row;
                }
            }

            // Display completion status.
            $table = new html_table();
            $table->attributes = ['style' => 'font-size: 80%;'];

            $row = new html_table_row();
            $row->cells[0] = new html_table_cell(html_writer::tag('b', get_string('cmid', 'block_course_activities')));
            $row->cells[1] = new html_table_cell(html_writer::tag('b', get_string('activity', 'block_course_activities')));
            $row->cells[2] = new html_table_cell(html_writer::tag('b', get_string('timecreated', 'block_course_activities')));
            $row->cells[3] = new html_table_cell(html_writer::tag('b', get_string('status', 'block_course_activities')));
            $rows[] = $row;

            $rows = array_merge($rows, $activities_rows);

            $table->data = $rows;
            $this->content->text .= html_writer::table($table);
        } else {
            $this->content->text = get_string('nottracked', 'completion');
        }

        return $this->content;
    }
}
