<?php

namespace WPTrait\Collection;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('Event')) {

    class Event
    {

        public function single($timestamp, $hook, $args = array(), $wp_error = false)
        {
            return wp_schedule_single_event($timestamp, $hook, $args, $wp_error);
        }

        public function add($timestamp, $recurrence, $hook, $args = array(), $wp_error = false)
        {
            return wp_schedule_event($timestamp, $recurrence, $hook, $args, $wp_error);
        }

        public function schedules()
        {
            return wp_get_schedules();
        }

        public function delete($hook)
        {
            return wp_clear_scheduled_hook($hook);
        }

        public function next($hook)
        {
            return wp_next_scheduled($hook);
        }

    }
}