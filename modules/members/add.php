<?php
/**
 * Chama Management Platform - Add New Member
 * 
 * Form to add a new member to the chama group
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

define('CHAMA_ACCESS', true);
require_once '../../config/config.php';

// Ensure user is logged in and has permission
requireLogin();
requirePermission('manage_members');

$pageTitle = 'Add New Member';
$currentUser = currentUser();
$chamaGroupId = currentChamaGroup();

$error = '';
$success = '';
$formData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!session()->validateCsrfToken($_POST['_token'] ?? '')) {
        $error = 'Invalid request token';
    } else {
        // Collect and validate form data
        $formData = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'id_number' => trim($_POST['id_number'] ?? ''),
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'address' => trim($_POST['address'] ?? ''),
            'occupation' => trim($_POST['occupation'] ?? ''),
            'emergency_contact_name' => trim($_POST['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => trim($_POST['emergency_contact_phone'] ?? ''),
            'next_of_kin' => trim($_POST['next_of_kin'] ?? ''),
            'relationship_to_kin' => trim($_POST['relationship_to_kin'] ?? ''),
            'membership_date' => $_POST['membership_date'] ?? date('Y-m-d'),
            'membership_fee_paid' => (float)($_POST['membership_fee_paid'] ?? 0),
            'initial_savings' => (float)($_POST['initial_savings'] ?? 0),
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        // Validation
        $errors = [];
        
        if (empty($formData['full_name'])) {
            $errors[] = 'Full name is required';
        }
        
        if (empty($formData['phone'])) {
            $errors[] = 'Phone number is required';
        } elseif (!isValidPhone($formData['phone'])) {
            $errors[] = 'Please enter a valid phone number';
        }
        
        if (!empty($formData['email']) && !isValidEmail($formData['email'])) {
            $errors[] = 'Please enter a valid email address';
        }
        
        if (!empty($formData['date_of_birth'])) {
            $birthDate = strtotime($formData['date_of_birth']);
            $minAge = strtotime('-16 years');
            if ($birthDate > $minAge) {
                $errors[] = 'Member must be at least 16 years old';
            }
        }
        
        if (empty($formData['membership_date'])) {
            $errors[] = 'Membership date is required';
        }
        
        if (empty($errors)) {
            try {
                $db = Database::getInstance();
                $db->beginTransaction();
                
                // Check for duplicate phone number
                $existingMember = $db->fetchOne(
                    "SELECT id FROM members WHERE phone = ? AND chama_group_id = ?",
                    [formatPhone($formData['phone']), $chamaGroupId]
                );
                
                if ($existingMember) {
                    throw new Exception('A member with this phone number already exists');
                }
                
                // Check for duplicate email if provided
                if (!empty($formData['email'])) {
                    $existingEmail = $db->fetchOne(
                        "SELECT id FROM members WHERE email = ? AND chama_group_id = ?",
                        [$formData['email'], $chamaGroupId]
                    );
                    
                    if ($existingEmail) {
                        throw new Exception('A member with this email address already exists');
                    }
                }
                
                // Generate unique member number
                $memberNumber = generateMemberNumber($chamaGroupId);
                
                // Handle photo upload
                $photoPath = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = handleFileUpload($_FILES['photo'], 'member_photos', ALLOWED_IMAGE_TYPES);
                    if ($uploadResult['success']) {
                        $photoPath = $uploadResult['filename'];
                    } else {
                        throw new Exception('Photo upload failed: ' . $uploadResult['message']);
                    }
                }
                
                // Insert member
                $memberId = $db->execute(
                    "INSERT INTO members (
                        chama_group_id, member_number, full_name, email, phone, id_number, 
                        date_of_birth, gender, address, occupation, emergency_contact_name, 
                        emergency_contact_phone, next_of_kin, relationship_to_kin, photo, 
                        membership_date, membership_fee_paid, notes, created_by, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')",
                    [
                        $chamaGroupId,
                        $memberNumber,
                        $formData['full_name'],
                        $formData['email'] ?: null,
                        formatPhone($formData['phone']),
                        $formData['id_number'] ?: null,
                        $formData['date_of_birth'] ?: null,
                        $formData['gender'] ?: null,
                        $formData['address'] ?: null,
                        $formData['occupation'] ?: null,
                        $formData['emergency_contact_name'] ?: null,
                        $formData['emergency_contact_phone'] ? formatPhone($formData['emergency_contact_phone']) : null,
                        $formData['next_of_kin'] ?: null,
                        $formData['relationship_to_kin'] ?: null,
                        $photoPath,
                        $formData['membership_date'],
                        $formData['membership_fee_paid'],
                        $formData['notes'] ?: null,
                        $currentUser['id']
                    ]
                );
                
                $memberId = $db->lastInsertId();
                
                // Create member savings account
                createMemberSavingsAccount($memberId);
                
                // Record initial savings if provided
                if ($formData['initial_savings'] > 0) {
                    $transactionResult = recordTransaction([
                        'chama_group_id' => $chamaGroupId,
                        'transaction_type' => 'Deposit',
                        'amount' => $formData['initial_savings'],
                        'description' => 'Initial savings deposit',
                        'member_id' => $memberId,
                        'payment_method' => 'Cash',
                        'processed_by' => $currentUser['id'],
                        'status' => 'Completed'
                    ]);
                    
                    if (!$transactionResult['success']) {
                        throw new Exception('Failed to record initial savings: ' . $transactionResult['message']);
                    }
                }
                
                // Record membership fee payment if provided
                if ($formData['membership_fee_paid'] > 0) {
                    recordTransaction([
                        'chama_group_id' => $chamaGroupId,
                        'transaction_type' => 'Deposit',
                        'amount' => $formData['membership_fee_paid'],
                        'description' => 'Membership fee payment',
                        'member_id' => $memberId,
                        'payment_method' => 'Cash',
                        'processed_by' => $currentUser['id'],
                        'status' => 'Completed'
                    ]);
                }
                
                $db->commit();
                
                // Clear form data
                $formData = [];
                $success = "Member '{$formData['full_name']}' has been successfully added with member number: $memberNumber";
                
                // Redirect to member view page
                redirect("view.php?id=$memberId", $success);
                
            } catch (Exception $e) {
                $db->rollback();
                $error = $e->getMessage();
                logError("Failed to add member: " . $e->getMessage());
            }
        } else {
            $error = implode(', ', $errors);
        }
    }
}

include_once '../../includes/header.php';
?>

<style>
    .add-member-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0;
    }
    
    .page-header {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    [data-theme="dark"] .page-header {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }
    
    [data-theme="dark"] .page-title {
        color: var(--gray-100);
    }
    
    .member-form-container {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
        overflow: hidden;
    }
    
    [data-theme="dark"] .member-form-container {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .form-header {
        background: var(--gray-50);
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--gray-200);
    }
    
    [data-theme="dark"] .form-header {
        background: var(--gray-900);
        border-color: var(--gray-700);
    }
    
    .form-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
    }
    
    [data-theme="dark"] .form-header h3 {
        color: var(--gray-100);
    }
    
    .form-content {
        padding: 2rem;
    }
    
    .form-section {
        margin-bottom: 2rem;
    }
    
    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--primary-500);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    [data-theme="dark"] .section-title {
        color: var(--gray-100);
    }
    
    .section-icon {
        color: var(--primary-500);
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .form-grid.single-column {
        grid-template-columns: 1fr;
    }
    
    .form-grid.two-column {
        grid-template-columns: repeat(2, 1fr);
    }
    
    @media (max-width: 768px) {
        .form-grid,
        .form-grid.two-column {
            grid-template-columns: 1fr;
        }
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    [data-theme="dark"] .form-label {
        color: var(--gray-300);
    }
    
    .required {
        color: var(--error-500);
    }
    
    .form-input,
    .form-select,
    .form-textarea {
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 0.875rem;
        background: white;
        color: var(--gray-900);
        transition: all 0.3s ease;
    }
    
    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary-500);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    [data-theme="dark"] .form-input,
    [data-theme="dark"] .form-select,
    [data-theme="dark"] .form-textarea {
        background: var(--gray-700);
        border-color: var(--gray-600);
        color: var(--gray-100);
    }
    
    .form-textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .file-upload {
        position: relative;
        display: inline-block;
        cursor: pointer;
        width: 100%;
    }
    
    .file-input {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-upload-area {
        border: 2px dashed var(--gray-300);
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        background: var(--gray-50);
    }
    
    .file-upload-area:hover,
    .file-upload-area.dragover {
        border-color: var(--primary-500);
        background: var(--primary-50);
    }
    
    [data-theme="dark"] .file-upload-area {
        background: var(--gray-700);
        border-color: var(--gray-600);
    }
    
    [data-theme="dark"] .file-upload-area:hover,
    [data-theme="dark"] .file-upload-area.dragover {
        background: rgba(59, 130, 246, 0.1);
        border-color: var(--primary-400);
    }
    
    .file-upload-icon {
        font-size: 2rem;
        color: var(--gray-400);
        margin-bottom: 0.5rem;
    }
    
    .file-upload-text {
        color: var(--gray-600);
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }
    
    [data-theme="dark"] .file-upload-text {
        color: var(--gray-400);
    }
    
    .file-upload-subtext {
        color: var(--gray-500);
        font-size: 0.75rem;
    }
    
    .photo-preview {
        margin-top: 1rem;
        text-align: center;
    }
    
    .photo-preview img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--gray-200);
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding: 1.5rem 2rem;
        border-top: 1px solid var(--gray-200);
        background: var(--gray-50);
    }
    
    [data-theme="dark"] .form-actions {
        border-color: var(--gray-700);
        background: var(--gray-900);
    }
    
    @media (max-width: 480px) {
        .form-actions {
            flex-direction: column;
        }
        
        .form-actions .btn {
            width: 100%;
        }
    }
    
    .form-help {
        font-size: 0.75rem;
        color: var(--gray-500);
        margin-top: 0.25rem;
    }
    
    [data-theme="dark"] .form-help {
        color: var(--gray-400);
    }
    
    .currency-input {
        position: relative;
    }
    
    .currency-symbol {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-500);
        font-weight: 500;
        pointer-events: none;
    }
    
    .currency-input .form-input {
        padding-left: 2.5rem;
    }
    
    .phone-input {
        position: relative;
    }
    
    .country-code {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-500);
        font-size: 0.875rem;
        pointer-events: none;
    }
    
    .phone-input .form-input {
        padding-left: 3.5rem;
    }
</style>

<div class="add-member-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Add New Member</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Members
        </a>
    </div>
    
    <!-- Member Form -->
    <div class="member-form-container">
        <div class="form-header">
            <h3>Member Information</h3>
            <p style="margin: 0.5rem 0 0; color: var(--gray-600); font-size: 0.875rem;">
                Fill in the member details below. Fields marked with <span class="required">*</span> are required.
            </p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error m-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success m-4">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="memberForm" class="form-content">
            <?php echo csrfField(); ?>
            
            <!-- Personal Information Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fas fa-user section-icon"></i>
                    Personal Information
                </h4>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            Full Name <span class="required">*</span>
                        </label>
                        <input type="text" name="full_name" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['full_name'] ?? ''); ?>" 
                               required maxlength="100" placeholder="Enter full name">
                        <div class="form-help">Enter the member's complete legal name</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Email Address
                        </label>
                        <input type="email" name="email" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" 
                               maxlength="100" placeholder="member@example.com">
                        <div class="form-help">Optional: Used for notifications and communication</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Phone Number <span class="required">*</span>
                        </label>
                        <div class="phone-input">
                            <span class="country-code">+254</span>
                            <input type="tel" name="phone" class="form-input" 
                                   value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>" 
                                   required placeholder="700000000">
                        </div>
                        <div class="form-help">Enter Kenyan mobile number without country code</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            ID Number
                        </label>
                        <input type="text" name="id_number" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['id_number'] ?? ''); ?>" 
                               maxlength="20" placeholder="12345678">
                        <div class="form-help">National ID or passport number</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Date of Birth
                        </label>
                        <input type="date" name="date_of_birth" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['date_of_birth'] ?? ''); ?>" 
                               max="<?php echo date('Y-m-d', strtotime('-16 years')); ?>">
                        <div class="form-help">Must be at least 16 years old</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Gender
                        </label>
                        <select name="gender" class="form-select">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($formData['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($formData['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($formData['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid single-column">
                    <div class="form-group">
                        <label class="form-label">
                            Address
                        </label>
                        <textarea name="address" class="form-textarea" 
                                  placeholder="Enter physical address"><?php echo htmlspecialchars($formData['address'] ?? ''); ?></textarea>
                        <div class="form-help">Physical address or location</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Occupation
                        </label>
                        <input type="text" name="occupation" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['occupation'] ?? ''); ?>" 
                               maxlength="100" placeholder="e.g., Teacher, Business Owner">
                        <div class="form-help">Current job or profession</div>
                    </div>
                </div>
            </div>
            
            <!-- Emergency Contact Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fas fa-phone section-icon"></i>
                    Emergency Contact
                </h4>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            Emergency Contact Name
                        </label>
                        <input type="text" name="emergency_contact_name" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['emergency_contact_name'] ?? ''); ?>" 
                               maxlength="100" placeholder="Full name">
                        <div class="form-help">Person to contact in case of emergency</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Emergency Contact Phone
                        </label>
                        <div class="phone-input">
                            <span class="country-code">+254</span>
                            <input type="tel" name="emergency_contact_phone" class="form-input" 
                                   value="<?php echo htmlspecialchars($formData['emergency_contact_phone'] ?? ''); ?>" 
                                   placeholder="700000000">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Next of Kin
                        </label>
                        <input type="text" name="next_of_kin" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['next_of_kin'] ?? ''); ?>" 
                               maxlength="100" placeholder="Full name">
                        <div class="form-help">Legal next of kin</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Relationship to Next of Kin
                        </label>
                        <input type="text" name="relationship_to_kin" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['relationship_to_kin'] ?? ''); ?>" 
                               maxlength="50" placeholder="e.g., Spouse, Parent, Sibling">
                    </div>
                </div>
            </div>
            
            <!-- Membership Details Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fas fa-id-card section-icon"></i>
                    Membership Details
                </h4>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            Membership Date <span class="required">*</span>
                        </label>
                        <input type="date" name="membership_date" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['membership_date'] ?? date('Y-m-d')); ?>" 
                               required max="<?php echo date('Y-m-d'); ?>">
                        <div class="form-help">Date when member joined the chama</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Membership Fee Paid
                        </label>
                        <div class="currency-input">
                            <span class="currency-symbol">KSh</span>
                            <input type="number" name="membership_fee_paid" class="form-input" 
                                   value="<?php echo htmlspecialchars($formData['membership_fee_paid'] ?? ''); ?>" 
                                   min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="form-help">Registration fee paid by the member</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Initial Savings Deposit
                        </label>
                        <div class="currency-input">
                            <span class="currency-symbol">KSh</span>
                            <input type="number" name="initial_savings" class="form-input" 
                                   value="<?php echo htmlspecialchars($formData['initial_savings'] ?? ''); ?>" 
                                   min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="form-help">Optional initial savings amount</div>
                    </div>
                </div>
            </div>
            
            <!-- Photo Upload Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fas fa-camera section-icon"></i>
                    Member Photo
                </h4>
                
                <div class="form-group">
                    <label class="form-label">
                        Upload Photo
                    </label>
                    <div class="file-upload">
                        <input type="file" name="photo" class="file-input" 
                               accept="<?php echo implode(',', array_map(function($ext) { return ".$ext"; }, ALLOWED_IMAGE_TYPES)); ?>"
                               id="photoInput">
                        <div class="file-upload-area" id="photoUploadArea">
                            <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                            <div class="file-upload-text">Click to upload or drag and drop</div>
                            <div class="file-upload-subtext">
                                JPG, PNG, GIF up to <?php echo number_format(MAX_IMAGE_SIZE / 1024 / 1024, 1); ?>MB
                            </div>
                        </div>
                    </div>
                    <div class="photo-preview" id="photoPreview" style="display: none;">
                        <img id="photoPreviewImage" alt="Photo preview">
                        <div style="margin-top: 0.5rem;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="removePhoto()">
                                <i class="fas fa-times mr-1"></i>
                                Remove Photo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Notes Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fas fa-sticky-note section-icon"></i>
                    Additional Notes
                </h4>
                
                <div class="form-group">
                    <label class="form-label">
                        Notes
                    </label>
                    <textarea name="notes" class="form-textarea" rows="4" 
                              placeholder="Any additional information about the member..."><?php echo htmlspecialchars($formData['notes'] ?? ''); ?></textarea>
                    <div class="form-help">Optional notes or special considerations</div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo mr-2"></i>
                    Reset Form
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save mr-2"></i>
                    Add Member
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form
    initializePhotoUpload();
    initializeFormValidation();
    initializeAutoSave();
    
    // Restore auto-saved data
    if (restoreAutoSave('memberForm')) {
        showToast('Form data restored from auto-save', 'info', null, 3000);
    }
});

function initializePhotoUpload() {
    const photoInput = document.getElementById('photoInput');
    const uploadArea = document.getElementById('photoUploadArea');
    const preview = document.getElementById('photoPreview');
    const previewImage = document.getElementById('photoPreviewImage');
    
    // File input change
    photoInput.addEventListener('change', handlePhotoSelect);
    
    // Drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            photoInput.files = files;
            handlePhotoSelect({ target: photoInput });
        }
    });
    
    function handlePhotoSelect(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Validate file type
        const allowedTypes = <?php echo json_encode(ALLOWED_IMAGE_TYPES); ?>;
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(fileExtension)) {
            showToast('Please select a valid image file (JPG, PNG, GIF)', 'error');
            e.target.value = '';
            return;
        }
        
        // Validate file size
        const maxSize = <?php echo MAX_IMAGE_SIZE; ?>;
        if (file.size > maxSize) {
            showToast(`File size must be less than ${(maxSize / 1024 / 1024).toFixed(1)}MB`, 'error');
            e.target.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            preview.style.display = 'block';
            uploadArea.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}

function removePhoto() {
    document.getElementById('photoInput').value = '';
    document.getElementById('photoPreview').style.display = 'none';
    document.getElementById('photoUploadArea').style.display = 'block';
}

function initializeFormValidation() {
    const form = document.getElementById('memberForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding Member...';
        
        // Clear auto-save on successful submission
        setTimeout(() => {
            clearAutoSave('memberForm');
        }, 1000);
    });
    
    // Real-time validation
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', validateField);
        field.addEventListener('input', clearFieldError);
    });
    
    // Phone number formatting
    const phoneInputs = form.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Remove leading zero if present
            if (value.startsWith('0')) {
                value = value.substring(1);
            }
            
            // Limit to 9 digits for Kenyan numbers
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            
            e.target.value = value;
        });
    });
}

function validateForm() {
    let isValid = true;
    const form = document.getElementById('memberForm');
    
    // Clear previous errors
    form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    form.querySelectorAll('.error-message').forEach(el => el.remove());
    
    // Validate required fields
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
    });
    
    // Validate email
    const emailField = form.querySelector('input[name="email"]');
    if (emailField.value && !isValidEmail(emailField.value)) {
        showFieldError(emailField, 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate phone numbers
    const phoneFields = form.querySelectorAll('input[type="tel"]');
    phoneFields.forEach(field => {
        if (field.value && !isValidPhone(field.value)) {
            showFieldError(field, 'Please enter a valid phone number');
            isValid = false;
        }
    });
    
    // Validate date of birth
    const dobField = form.querySelector('input[name="date_of_birth"]');
    if (dobField.value) {
        const birthDate = new Date(dobField.value);
        const minAge = new Date();
        minAge.setFullYear(minAge.getFullYear() - 16);
        
        if (birthDate > minAge) {
            showFieldError(dobField, 'Member must be at least 16 years old');
            isValid = false;
        }
    }
    
    return isValid;
}

function validateField(e) {
    const field = e.target;
    clearFieldError(field);
    
    if (field.hasAttribute('required') && !field.value.trim()) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    if (field.type === 'tel' && field.value && !isValidPhone(field.value)) {
        showFieldError(field, 'Please enter a valid phone number');
        return false;
    }
    
    return true;
}

function showFieldError(field, message) {
    field.classList.add('error');
    field.style.borderColor = 'var(--error-500)';
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.cssText = 'color: var(--error-500); font-size: 0.75rem; margin-top: 0.25rem;';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    field.style.borderColor = '';
    
    const errorMessage = field.parentNode.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    // Kenyan phone number validation (9 digits without country code)
    const phoneRegex = /^[7][0-9]{8}$/;
    return phoneRegex.test(phone);
}

function initializeAutoSave() {
    // Auto-save form data every 30 seconds
    autoSaveForm('memberForm', 30000);
}

function resetForm() {
    confirmAction(
        'Are you sure you want to reset the form? All entered data will be lost.',
        function() {
            document.getElementById('memberForm').reset();
            removePhoto();
            clearAutoSave('memberForm');
            
            // Clear any errors
            document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            
            showToast('Form has been reset', 'info');
        },
        'Reset Form'
    );
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+S to save (submit form)
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('memberForm').submit();
    }
    
    // Ctrl+R to reset form
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        resetForm();
    }
    
    // Escape to go back
    if (e.key === 'Escape') {
        window.location.href = 'index.php';
    }
});

// Form submission enhancement
document.getElementById('memberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateForm()) {
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitBtn');
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding Member...';
    
    showLoading();
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.text();
        }
        throw new Error('Network response was not ok');
    })
    .then(data => {
        hideLoading();
        
        // Check if response indicates success (redirect or success message)
        if (data.includes('successfully added') || data.includes('view.php')) {
            clearAutoSave('memberForm');
            showToast('Member added successfully!', 'success');
            
            setTimeout(() => {
                // Redirect to members list or member view
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('redirect') === 'view') {
                    // Extract member ID from response if available
                    const memberIdMatch = data.match(/view\.php\?id=(\d+)/);
                    if (memberIdMatch) {
                        window.location.href = `view.php?id=${memberIdMatch[1]}`;
                    } else {
                        window.location.href = 'index.php';
                    }
                } else {
                    window.location.href = 'index.php';
                }
            }, 1500);
        } else {
            // Parse error from response
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');
            const errorElement = doc.querySelector('.alert-error');
            
            if (errorElement) {
                const errorText = errorElement.textContent.trim();
                showToast(errorText, 'error');
            } else {
                showToast('An error occurred while adding the member', 'error');
            }
            
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Add Member';
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('An error occurred while adding the member', 'error');
        
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Add Member';
    });
});

// Page-specific initialization
window.initializePage = function() {
    console.log('Add Member page initialized');
    
    // Focus on first input
    const firstInput = document.querySelector('.form-input');
    if (firstInput) {
        firstInput.focus();
    }
    
    // Set up form progress tracking
    trackFormProgress();
};

function trackFormProgress() {
    const form = document.getElementById('memberForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    let completedFields = 0;
    
    function updateProgress() {
        completedFields = 0;
        inputs.forEach(input => {
            if (input.value.trim()) {
                completedFields++;
            }
        });
        
        const progress = Math.round((completedFields / inputs.length) * 100);
        
        // Update page title with progress
        if (progress > 0) {
            document.title = `Add Member (${progress}%) - ${document.title.split(' - ').pop()}`;
        }
    }
    
    inputs.forEach(input => {
        input.addEventListener('input', updateProgress);
        input.addEventListener('change', updateProgress);
    });
    
    updateProgress();
}

// Warning before leaving page with unsaved changes
let formChanged = false;

document.getElementById('memberForm').addEventListener('input', function() {
    formChanged = true;
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        const message = 'You have unsaved changes. Are you sure you want to leave?';
        e.returnValue = message;
        return message;
    }
});

// Clear the warning when form is submitted
document.getElementById('memberForm').addEventListener('submit', function() {
    formChanged = false;
});
</script>

<?php include_once '../../includes/footer.php'; ?>