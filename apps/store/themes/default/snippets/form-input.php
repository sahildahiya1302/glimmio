<?php
// Form Input snippet template

// Load global settings
$settings = json_decode(file_get_contents(__DIR__ . '/../config/settings_data.json'), true);

// Load schema for editor UI
$schema = [
    'name' => 'Form Input',
    'settings' => [
        ['type' => 'text', 'id' => 'label', 'label' => 'Label', 'default' => 'Input Label'],
        ['type' => 'text', 'id' => 'name', 'label' => 'Input Name', 'default' => 'input_name'],
        ['type' => 'text', 'id' => 'placeholder', 'label' => 'Placeholder', 'default' => 'Enter text'],
        ['type' => 'select', 'id' => 'type', 'label' => 'Input Type', 'options' => [
            ['value' => 'text', 'label' => 'Text'],
            ['value' => 'email', 'label' => 'Email'],
            ['value' => 'password', 'label' => 'Password'],
            ['value' => 'number', 'label' => 'Number'],
        ], 'default' => 'text'],
        ['type' => 'checkbox', 'id' => 'required', 'label' => 'Required', 'default' => false],
    ],
];

// Extract settings
$label = $settings['label'] ?? 'Input Label';
$name = $settings['name'] ?? 'input_name';
$placeholder = $settings['placeholder'] ?? 'Enter text';
$type = $settings['type'] ?? 'text';
$required = $settings['required'] ?? false;

?>
<div class="form-input">
    <label for="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($label); ?></label>
    <input 
        type="<?php echo htmlspecialchars($type); ?>" 
        id="<?php echo htmlspecialchars($name); ?>" 
        name="<?php echo htmlspecialchars($name); ?>" 
        placeholder="<?php echo htmlspecialchars($placeholder); ?>" 
        <?php echo $required ? 'required' : ''; ?>
    />
</div>
