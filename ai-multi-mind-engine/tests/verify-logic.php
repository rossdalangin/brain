<?php
/**
 * Logic Verification Script (CLI)
 * Simulates the AI generation and payment flow.
 */

// Mock WordPress environment
define( 'ABSPATH', true );
function add_action($tag, $callback) {}
function get_option($key, $default = '') { return $default; }
function update_user_meta($id, $key, $val) { echo "META UPDATED: $key -> $val\n"; }
function get_user_meta($id, $key, $single = true) { return ''; }
function is_user_logged_in() { return true; }
function get_current_user_id() { return 1; }
function wp_remote_post($url, $args) { return array('body' => json_encode(array('candidates' => array(array('content' => array('parts' => array(array('text' => 'MOCK AI RESPONSE')))))))); }
function wp_remote_retrieve_body($res) { return $res['body']; }
function is_wp_error($res) { return $res instanceof WP_Error; }
function wp_insert_post($args) { echo "POST INSERTED: " . $args['post_title'] . "\n"; return 123; }
function rest_ensure_response($data) { return $data; }

class WP_Error { public function __construct($c, $m) { $this->c = $c; $this->m = $m; } }

// Mock $wpdb
class Mock_WPDB {
    public $prefix = 'wp_';
    public function insert($t, $d) { echo "DB INSERT into $t\n"; }
    public function update($t, $d, $w) { echo "DB UPDATE $t\n"; }
    public function get_var($q) { return 0; }
    public function query($q) { echo "DB QUERY: $q\n"; }
    public function prepare($q, ...$args) { return $q; }
}
$wpdb = new Mock_WPDB();

// Include classes
require_once __DIR__ . '/../includes/minds/class-mind-base.php';
require_once __DIR__ . '/../includes/minds/class-mind-ceo.php';
require_once __DIR__ . '/../includes/class-ai-provider-manager.php';
require_once __DIR__ . '/../includes/class-prompt-engine.php';
require_once __DIR__ . '/../includes/class-usage-tracker.php';
require_once __DIR__ . '/../includes/class-rest-api.php';

echo "--- Testing Prompt Engine ---\n";
$engine = new AMM_Prompt_Engine();
$prompts = $engine->prepare_prompts('ceo', 'business_plan', 'Scale my startup');
echo "System Prompt length: " . strlen($prompts['system']) . "\n";

echo "\n--- Testing AI Provider Manager (Mock) ---\n";
$ai = new AMM_AI_Provider_Manager();
$res = $ai->generate_response('gemini', $prompts['system'], $prompts['user']);
if (is_wp_error($res)) {
    echo "AI Error: " . $res->m . "\n";
} else {
    echo "AI Response: $res\n";
}

echo "\n--- Testing Usage Tracker ---\n";
$tracker = new AMM_Usage_Tracker();
echo "Can generate? " . ($tracker->can_user_generate(1) ? 'Yes' : 'No') . "\n";
$tracker->track_generation(1);

echo "\n--- Testing REST API (Mock) ---\n";
$api = new AMM_REST_API();
$mock_request = new class {
    public function get_json_params() {
        return array('mind_id' => 'ceo', 'output_type' => 'business_plan', 'user_input' => 'Scale me');
    }
};
$api_res = $api->handle_generation($mock_request);
print_r($api_res);

echo "\nVerification Complete!\n";
