<?php
defined('MOODLE_INTERNAL') || die();
class mod_customactivity_renderer extends plugin_renderer_base {
    public function render_customactivity(\stdClass $activity) {
        $o = html_writer::start_div('customactivity-render');
        $o .= html_writer::tag('h3', format_string($activity->name));
        $o .= format_text($activity->intro);
        $o .= html_writer::end_div();
        return $o;
    }
}
