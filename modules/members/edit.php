
header-actions {
        display: flex;
        gap: 1rem;
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
        display: flex;
        align-items: center;
        justify-content: space-between;
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
    
    .member-badge {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 1rem;
        background: var(--primary-100);
        color: var(--primary-700);
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    [data-theme="dark"] .member-badge {
        background: rgba(59, 130, 246, 0.2);
        color: var(--primary-400);
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
        
        .header-actions {
            flex-direction: column;
        }
        
        .page-header {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
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
    
    .current-photo {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: 8px;
        border: 1px solid var(--gray-200);
    }
    
    [data-theme="dark"] .current-photo {
        background: var(--gray-700);
        border-color: var(--gray-600);
    }
    
    .current-photo img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--gray-300);
    }
    
    .current-photo-info {
        flex: 1;
    }
    
    .current-photo-title {
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }
    
    [data-theme="dark"] .current-photo-title {
        color: var(--gray-100);
    }
    
    .current-photo-meta {
        font-size: 0.8rem;
        color: var(--gray-500);
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
    
    .status-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-top: 0.5rem;
    }
    
    @media (max-width: 480px) {
        .status-options {
            grid-template-columns: 1fr;
        }
    }
    
    .status-option {
        position: relative;
    }
    
    .status-radio {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .status-card {
        padding: 1rem;
        border: 2px solid var(--gray-300);
        border-radius: 8px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: white;
    }
    
    .status-card:hover {
        border-color: var(--primary-500);
        background: var(--primary-50);
    }
    
    .status-radio:checked + .status-card {
        border-color: var(--primary-500);
        background: var(--primary-100);
    }
    
    [data-theme="dark"] .status-card {
        background: var(--gray-700);
        border-color: var(--gray-600);
    }
    
    [data-theme="dark"] .status-card:hover {
        background: rgba(59, 130, 246, 0.1);
        border-color: var(--primary-400);
    }
    
    [data-theme="dark"] .status-radio:checked + .status-card {
        background: rgba(59, 130, 246, 0.2);
        border-color: var(--primary-400);
    }
    
    .status-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }
    
    .status-icon.active {
        color: var(--success-500);
    }
    
    .status-icon.inactive {
        color: var(--gray-500);
    }
    
    .status-icon.suspended {
        color: var(--error-500);
    }
    
    .status-title {
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }
    
    [data-theme="dark"] .status-title {
        color: var(--gray-100);
    }
    
    .status-desc {
        font-size: 0.8rem;
        color: var(--gray-600);
    }
    
    [data-theme="dark"] .status-desc {
        color: var(--gray-400);
    }
</style>

<div class="edit-member-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Edit Member</h1>
        <div class="header-actions">
            <a href="view.php?id=<?php echo $memberId; ?>" class="btn btn-secondary">
                <i class="fas fa-eye mr-2"></i>
                View Profile
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Members
            </a>
        </div>
    </div>
    
    <!-- Member Form -->
    <div class="member-form-container">
        <div class="form-header">
            <h3>Edit Member Information</h3>
            <div class="member-badge">
                <i class="fas fa-id-badge"></i>
                <span><?php echo htmlspecialchars($member['member_number']); ?></span>
            </div>
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
        
        <form method="POST" enctype="multipart/form-data" id="editMemberForm" class="form-content">
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
                               value="<?php echo htmlspecialchars($member['full_name']); ?>" 
                               required maxlength="100" placeholder="Enter full name">
                        <div class="form-help">Enter the member's complete legal name</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Email Address
                        </label>
                        <input type="email" name="email" class="form-input" 
                               value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>" 
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
                                   value="<?php echo htmlspecialchars(ltrim($member['phone'], '+254')); ?>" 
                                   required placeholder="700000000">
                        </div>
                        <div class="form-help">Enter Kenyan mobile number without country code</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            ID Number
                        </label>
                        <input type="text" name="id_number" class="form-input" 
                               value="<?php echo htmlspecialchars($member['id_number'] ?? ''); ?>" 
                               maxlength="20" placeholder="12345678">
                        <div class="form-help">National ID or passport number</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Date of Birth
                        </label>
                        <input type="date" name="date_of_birth" class="form-input" 
                               value="<?php echo htmlspecialchars($member['date_of_birth'] ?? ''); ?>" 
                               max="<?php echo date('Y-m-d', strtotime('-16 years')); ?>">
                        <div class="form-help">Must be at least 16 years old</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Gender
                        </label>
                        <select name="gender" class="form-select">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($member['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($member['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($member['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid single-column">
                    <div class="form-group">
                        <label class="form-label">
                            Address
                        </label>
                        <textarea name="address" class="form-textarea" 
                                  placeholder="Enter physical address"><?php echo htmlspecialchars($member['address'] ?? ''); ?></textarea>
                        <div class="form-help">Physical address or location</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Occupation
                        </label>
                        <input type="text" name="occupation" class="form-input" 
                               value="<?php echo htmlspecialchars($member['occupation'] ?? ''); ?>" 
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
                               value="<?php echo htmlspecialchars($member['emergency_contact_name'] ?? ''); ?>" 
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
                                   value="<?php echo htmlspecialchars($member['emergency_contact_phone'] ? ltrim($member['emergency_contact_phone'], '+254') : ''); ?>" 
                                   placeholder="700000000">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Next of Kin
                        </label>
                        <input type="text" name="next_of_kin" class="form-input" 
                               value="<?php echo htmlspecialchars($member['next_of_kin'] ?? ''); ?>" 
                               maxlength="100" placeholder="Full name">
                        <div class="form-help">Legal next of kin</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Relationship to Next of Kin
                        </label>
                        <input type="text" name="relationship_to_kin" class="form-input" 
                               value="<?php echo htmlspecialchars($member['relationship_to_kin'] ?? ''); ?>" 
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
                               value="<?php echo htmlspecialchars($member['membership_date']); ?>" 
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
                                   value="<?php echo htmlspecialchars($member['membership_fee_paid'] ?? '0'); ?>" 
                                   min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="form-help">Registration fee paid by the member</div>
                    </div>
                </div>
            </div>
            
            <!-- Member Status Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fas fa-user-check section-icon"></i>
                    Member Status
                </h4>
                
                <div class="form-group">
                    <label class="form-label">
                        Status <span class="required">*</span>
                    </label>
                    <div class="status-options">
                        <div class="status-option">
                            <input type="radio" name="status" value="Active" class="status-radio" 
                                   id="status_active" <?php echo $member['status'] === 'Active' ? 'checked' : ''; ?>>
                            <label for="status_active" class="status-card">
                                <div class="status-icon active">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="status-title">Active</div>
                                <div class="status-desc">Member is active and participating</div>
                            </label>
                        </div>
                        
                        <div class="status-option">
                            <input type="radio" name="status" value="Inactive" class="status-radio" 
                                   id="status_inactive" <?php echo $member['status'] === 'Inactive' ? 'checked' : ''; ?>>
                            <label for="status_inactive" class="status-card">
                                <div class="status-icon inactive">
                                    <i class="fas fa-pause-circle"></i>
                                </div>
                                <div class="status-title">Inactive</div>
                                <div class="status-desc">Member is temporarily inactive</div>
                            </label>
                        </div>
                        
                        <div class="status-option">
                            <input type="radio" name="status" value="Suspended" class="status-radio" 
                                   id="status_suspended" <?php echo $member['status'] === 'Suspended' ? 'checked' : ''; ?>>
                            <label for="status_suspended" class="status-card">
                                <div class="status-icon suspended">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div class="status-title">Suspended</div>
                                <div class="status-desc">Member is suspended from activities</div>
                            </label>
                        </div>
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
                    <?php if ($member['photo']): ?>
                        <div class="current-photo">
                            <img src="<?php echo getUploadUrl('members') . '/' . $member['photo']; ?>" 
                                 alt="Current photo">
                            <div class="current-photo-info">
                                <div class="current-photo-title">Current Photo</div>
                                <div class="current-photo-meta">
                                    <?php echo htmlspecialchars($member['photo']); ?>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeCurrentPhoto()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <label class="form-label">
                        <?php echo $member['photo'] ? 'Change Photo' : 'Upload Photo'; ?>
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
                              placeholder="Any additional information about the member..."><?php echo htmlspecialchars($member['notes'] ?? ''); ?></textarea>
                    <div class="form-help">Optional notes or special considerations</div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='view.php?id=<?php echo $memberId; ?>'">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo mr-2"></i>
                    Reset Changes
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save mr-2"></i>
                    Update Member
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
    
    // Track if form has been modified
    let formModified = false;
    const form = document.getElementById('editMemberForm');
    
    form.addEventListener('input', function() {
        formModified = true;
    });
    
    form.addEventListener('change', function() {
        formModified = true;
    });
    
    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (formModified) {
            const message = 'You have unsaved changes. Are you sure you want to leave?';
            e.returnValue = message;
            return message;
        }
    });
    
    // Clear warning when form is submitted
    form.addEventListener('submit', function() {
        formModified = false;
    });
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
    });<?php
/**
 * Chama Management Platform - Edit Member
 * 
 * Form to edit existing member details
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

define('CHAMA_ACCESS', true);
require_once '../../config/config.php';

// Ensure user is logged in and has permission
requireLogin();
requirePermission('manage_members');

$pageTitle = 'Edit Member';
$currentUser = currentUser();
$chamaGroupId = currentChamaGroup();

$memberId = (int)($_GET['id'] ?? 0);
$member = null;
$error = '';
$success = '';

if (!$memberId) {
    redirect('index.php', 'Invalid member ID', 'error');
}

// Get member details
try {
    $db = Database::getInstance();
    
    $member = $db->fetchOne(
        "SELECT * FROM members WHERE id = ? AND chama_group_id = ?",
        [$memberId, $chamaGroupId]
    );
    
    if (!$member) {
        redirect('index.php', 'Member not found', 'error');
    }
    
    $pageTitle = 'Edit ' . $member['full_name'];
    
} catch (Exception $e) {
    redirect('index.php', 'Failed to load member details', 'error');
}

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
            'membership_date' => $_POST['membership_date'] ?? '',
            'membership_fee_paid' => (float)($_POST['membership_fee_paid'] ?? 0),
            'status' => $_POST['status'] ?? '',
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
        
        if (!in_array($formData['status'], ['Active', 'Inactive', 'Suspended'])) {
            $errors[] = 'Invalid status selected';
        }
        
        if (empty($errors)) {
            try {
                $db->beginTransaction();
                
                // Check for duplicate phone number (excluding current member)
                $existingMember = $db->fetchOne(
                    "SELECT id FROM members WHERE phone = ? AND chama_group_id = ? AND id != ?",
                    [formatPhone($formData['phone']), $chamaGroupId, $memberId]
                );
                
                if ($existingMember) {
                    throw new Exception('A member with this phone number already exists');
                }
                
                // Check for duplicate email if provided (excluding current member)
                if (!empty($formData['email'])) {
                    $existingEmail = $db->fetchOne(
                        "SELECT id FROM members WHERE email = ? AND chama_group_id = ? AND id != ?",
                        [$formData['email'], $chamaGroupId, $memberId]
                    );
                    
                    if ($existingEmail) {
                        throw new Exception('A member with this email address already exists');
                    }
                }
                
                // Handle photo upload
                $photoPath = $member['photo']; // Keep existing photo by default
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = handleFileUpload($_FILES['photo'], 'member_photos', ALLOWED_IMAGE_TYPES);
                    if ($uploadResult['success']) {
                        // Delete old photo if exists
                        if ($member['photo'] && file_exists(getUploadPath('member_photos') . '/' . $member['photo'])) {
                            unlink(getUploadPath('member_photos') . '/' . $member['photo']);
                        }
                        $photoPath = $uploadResult['filename'];
                    } else {
                        throw new Exception('Photo upload failed: ' . $uploadResult['message']);
                    }
                }
                
                // Update member
                $db->execute(
                    "UPDATE members SET 
                        full_name = ?, email = ?, phone = ?, id_number = ?, 
                        date_of_birth = ?, gender = ?, address = ?, occupation = ?, 
                        emergency_contact_name = ?, emergency_contact_phone = ?, 
                        next_of_kin = ?, relationship_to_kin = ?, photo = ?, 
                        membership_date = ?, membership_fee_paid = ?, status = ?, notes = ?, 
                        updated_at = CURRENT_TIMESTAMP
                     WHERE id = ? AND chama_group_id = ?",
                    [
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
                        $formData['status'],
                        $formData['notes'] ?: null,
                        $memberId,
                        $chamaGroupId
                    ]
                );
                
                $db->commit();
                
                // Update member array with new data
                $member = array_merge($member, $formData);
                $member['photo'] = $photoPath;
                
                $success = "Member details updated successfully";
                
                // Redirect to member view page
                redirect("view.php?id=$memberId", $success);
                
            } catch (Exception $e) {
                $db->rollback();
                $error = $e->getMessage();
                logError("Failed to update member: " . $e->getMessage());
            }
        } else {
            $error = implode(', ', $errors);
        }
    }
}

include_once '../../includes/header.php';
?>

<style>
    .edit-member-container {
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
    }.header-actions {
        display: flex;
        gap: 1rem;
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
        display: flex;
        align-items: center;
        justify-content: space-between;
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
    
    .member-badge {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 1rem;
        background: var(--primary-100);
        color: var(--primary-700);
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    [data-theme="dark"] .member-badge {
        background: rgba(59, 130, 246, 0.2);
        color: var(--primary-400);
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
        
        .header-actions {
            flex-direction: column;
        }
        
        .page-header {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
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
    
    .current-photo {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: 8px;
        border: 1px solid var(--gray-200);
    }
    
    [data-theme="dark"] .current-photo {
        background: var(--gray-700);
        border-color: var(--gray-600);
    }
    
    .current-photo img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--gray-300);
    }
    
    .current-photo-info {
        flex: 1;
    }
    
    .current-photo-title {
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }
    
    [data-theme="dark"] .current-photo-title {
        color: var(--gray-100);
    }
    
    .current-photo-meta {
        font-size: 0.8rem;
        color: var(--gray-500);
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
    
    .status-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-top: 0.5rem;
    }
    
    @media (max-width: 480px) {
        .status-options {
            grid-template-columns: 1fr;
        }
    }
    
    .status-option {
        position: relative;
    }
    
    .status-radio {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .status-card {
        padding: 1rem;
        border: 2px solid var(--gray-300);
        border-radius: 8px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: white;
    }
    
    .status-card:hover {
        border-color: var(--primary-500);
        background: var(--primary-50);
    }
    
    .status-radio:checked + .status-card {
        border-color: var(--primary-500);
        background: var(--primary-100);
    }
    
    [data-theme="dark"] .status-card {
        background: var(--gray-700);
        border-color: var(--gray-600);
    }
    
    [data-theme="dark"] .status-card:hover {
        background: rgba(59, 130, 246, 0.1);
        border-color: var(--primary-400);
    }
    
    [data-theme="dark"] .status-radio:checked + .status-card {
        background: rgba(59, 130, 246, 0.2);
        border-color: var(--primary-400);
    }
    
    .status-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }
    
    .status-icon.active {
        color: var(--success-500);
    }
    
    .status-icon.inactive {
        color: var(--gray-500);
    }
    
    .status-icon.suspended {
        color: var(--error-500);
    }
    
    .status-title {
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }
    
    [data-theme="dark"] .status-title {
        color: var(--gray-100);
    }
    
    .status-desc {
        font-size: 0.8rem;
        color: var(--gray-600);
    }
    
    [data-theme="dark"] .status-desc {
        color: var(--gray-400);
    }
</style>

<div class="edit-member-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Edit Member</h1>
        <div class="header-actions">
            <a href="view.php?id=<?php echo $memberId; ?>" class="btn btn-secondary">
                <i class="fas fa-eye mr-2"></i>
                View Profile
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Members
            </a>
        </div>
    </div>
    
    <!-- Member Form -->
    <div class="member-form-container">
        <div class="form-header">
            <h3>Edit Member Information</h3>
            <div class="member-badge">
                <i class="fas fa-id-badge"></i>
                <span><?php echo htmlspecialchars($member['member_number']); ?></span>
            </div>
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
        
        <form method="POST" enctype="multipart/form-data" id="editMemberForm" class="form-content">
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
                               value="<?php echo htmlspecialchars($member['full_name']); ?>" 
                               required maxlength="100" placeholder="Enter full name">
                        <div class="form-help">Enter the member's complete legal name</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Email Address
                        </label>
                        <input type="email" name="email" class="form-input" 
                               value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>" 
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
                                   value="<?php echo htmlspecialchars(ltrim($member['phone'], '+254')); ?>" 
                                   required placeholder="700000000">
                        </div>
                        <div class="form-help">Enter Kenyan mobile number without country code</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            ID Number
                        </label>
                        <input type="text" name="id_number" class="form-input" 
                               value="<?php echo htmlspecialchars($member['id_number'] ?? ''); ?>" 
                               maxlength="20" placeholder="12345678">
                        <div class="form-help">National ID or passport number</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Date of Birth
                        </label>
                        <input type="date" name="date_of_birth" class="form-input" 
                               value="<?php echo htmlspecialchars($member['date_of_birth'] ?? ''); ?>" 
                               max="<?php echo date('Y-m-d', strtotime('-16 years')); ?>">
                        <div class="form-help">Must be at least 16 years old</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Gender
                        </label>
                        <select name="gender" class="form-select">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($member['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($member['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($member['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid single-column">
                    <div class="form-group">
                        <label class="form-label">
                            Address
                        </label>
                        <textarea name="address" class="form-textarea" 
                                  placeholder="Enter physical address"><?php echo htmlspecialchars($member['address'] ?? ''); ?></textarea>
                        <div class="form-help">Physical address or location</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Occupation
                        </label>
                        <input type="text" name="occupation" class="form-input" 
                               value="<?php echo htmlspecialchars($member['occupation'] ?? ''); ?>" 
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
                               value="<?php echo htmlspecialchars($member['emergency_contact_name'] ?? ''); ?>" 
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
                                   value="<?php echo htmlspecialchars($member['emergency_contact_phone'] ? ltrim($member['emergency_contact_phone'], '+254') : ''); ?>" 
                                   placeholder="700000000">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Next of Kin
                        </label>
                        <input type="text" name="next_of_kin" class="form-input" 
                               value="<?php echo htmlspecialchars($member['next_of_kin'] ?? ''); ?>" 
                               maxlength="100" placeholder="Full name">
                        <div class="form-help">Legal next of kin</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Relationship to Next of Kin
                        </label>
                        <input type="text" name="relationship_to_kin" class="form-input" 
                               value="<?php echo htmlspecialchars($member['relationship_to_kin'] ?? ''); ?>" 
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
                               value="<?php echo htmlspecialchars($member['membership_date']); ?>" 
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
                                   value="<?php echo htmlspecialchars($member['membership_fee_paid'] ?? '0'); ?>" 
                                   min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="form-help">Registration fee paid by the member</div>
                    </div>
                </div>
            </div>
            
            <!-- Member Status Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fas fa-user-check section-icon"></i>
                    Member Status
                </h4>
                
                <div class="form-group">
                    <label class="form-label">
                        Status <span class="required">*</span>
                    </label>
                    <div class="status-options">
                        <div class="status-option">
                            <input type="radio" name="status" value="Active" class="status-radio" 
                                   id="status_active" <?php echo $member['status'] === 'Active' ? 'checked' : ''; ?>>
                            <label for="status_active" class="status-card">
                                <div class="status-icon active">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="status-title">Active</div>
                                <div class="status-desc">Member is active and participating</div>
                            </label>
                        </div>
                        
                        <div class="status-option">
                            <input type="radio" name="status" value="Inactive" class="status-radio" 
                                   id="status_inactive" <?php echo $member['status'] === 'Inactive' ? 'checked' : ''; ?>>
                            <label for="status_inactive" class="status-card">
                                <div class="status-icon inactive">
                                    <i class="fas fa-pause-circle"></i>
                                </div>
                                <div class="status-title">Inactive</div>
                                <div class="status-desc">Member is temporarily inactive</div>
                            </label>
                        </div>
                        
                        <div class="status-option">
                            <input type="radio" name="status" value="Suspended" class="status-radio" 
                                   id="status_suspended" <?php echo $member['status'] === 'Suspended' ? 'checked' : ''; ?>>
                            <label for="status_suspended" class="status-card">
                                <div class="status-icon suspended">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div class="status-title">Suspended</div>
                                <div class="status-desc">Member is suspended from activities</div>
                            </label>
                        </div>
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
                    <?php if ($member['photo']): ?>
                        <div class="current-photo">
                            <img src="<?php echo getUploadUrl('members') . '/' . $member['photo']; ?>" 
                                 alt="Current photo">
                            <div class="current-photo-info">
                                <div class="current-photo-title">Current Photo</div>
                                <div class="current-photo-meta">
                                    <?php echo htmlspecialchars($member['photo']); ?>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeCurrentPhoto()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <label class="form-label">
                        <?php echo $member['photo'] ? 'Change Photo' : 'Upload Photo'; ?>
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
                              placeholder="Any additional information about the member..."><?php echo htmlspecialchars($member['notes'] ?? ''); ?></textarea>
                    <div class="form-help">Optional notes or special considerations</div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='view.php?id=<?php echo $memberId; ?>'">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo mr-2"></i>
                    Reset Changes
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save mr-2"></i>
                    Update Member
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
    
    // Track if form has been modified
    let formModified = false;
    const form = document.getElementById('editMemberForm');
    
    form.addEventListener('input', function() {
        formModified = true;
    });
    
    form.addEventListener('change', function() {
        formModified = true;
    });
    
    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (formModified) {
            const message = 'You have unsaved changes. Are you sure you want to leave?';
            e.returnValue = message;
            return message;
        }
    });
    
    // Clear warning when form is submitted
    form.addEventListener('submit', function() {
        formModified = false;
    });
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
    });<?php
/**
 * Chama Management Platform - Edit Member
 * 
 * Form to edit existing member details
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

define('CHAMA_ACCESS', true);
require_once '../../config/config.php';

// Ensure user is logged in and has permission
requireLogin();
requirePermission('manage_members');

$pageTitle = 'Edit Member';
$currentUser = currentUser();
$chamaGroupId = currentChamaGroup();

$memberId = (int)($_GET['id'] ?? 0);
$member = null;
$error = '';
$success = '';

if (!$memberId) {
    redirect('index.php', 'Invalid member ID', 'error');
}

// Get member details
try {
    $db = Database::getInstance();
    
    $member = $db->fetchOne(
        "SELECT * FROM members WHERE id = ? AND chama_group_id = ?",
        [$memberId, $chamaGroupId]
    );
    
    if (!$member) {
        redirect('index.php', 'Member not found', 'error');
    }
    
    $pageTitle = 'Edit ' . $member['full_name'];
    
} catch (Exception $e) {
    redirect('index.php', 'Failed to load member details', 'error');
}

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
            'membership_date' => $_POST['membership_date'] ?? '',
            'membership_fee_paid' => (float)($_POST['membership_fee_paid'] ?? 0),
            'status' => $_POST['status'] ?? '',
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
        
        if (!in_array($formData['status'], ['Active', 'Inactive', 'Suspended'])) {
            $errors[] = 'Invalid status selected';
        }
        
        if (empty($errors)) {
            try {
                $db->beginTransaction();
                
                // Check for duplicate phone number (excluding current member)
                $existingMember = $db->fetchOne(
                    "SELECT id FROM members WHERE phone = ? AND chama_group_id = ? AND id != ?",
                    [formatPhone($formData['phone']), $chamaGroupId, $memberId]
                );
                
                if ($existingMember) {
                    throw new Exception('A member with this phone number already exists');
                }
                
                // Check for duplicate email if provided (excluding current member)
                if (!empty($formData['email'])) {
                    $existingEmail = $db->fetchOne(
                        "SELECT id FROM members WHERE email = ? AND chama_group_id = ? AND id != ?",
                        [$formData['email'], $chamaGroupId, $memberId]
                    );
                    
                    if ($existingEmail) {
                        throw new Exception('A member with this email address already exists');
                    }
                }
                
                // Handle photo upload
                $photoPath = $member['photo']; // Keep existing photo by default
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = handleFileUpload($_FILES['photo'], 'member_photos', ALLOWED_IMAGE_TYPES);
                    if ($uploadResult['success']) {
                        // Delete old photo if exists
                        if ($member['photo'] && file_exists(getUploadPath('member_photos') . '/' . $member['photo'])) {
                            unlink(getUploadPath('member_photos') . '/' . $member['photo']);
                        }
                        $photoPath = $uploadResult['filename'];
                    } else {
                        throw new Exception('Photo upload failed: ' . $uploadResult['message']);
                    }
                }
                
                // Update member
                $db->execute(
                    "UPDATE members SET 
                        full_name = ?, email = ?, phone = ?, id_number = ?, 
                        date_of_birth = ?, gender = ?, address = ?, occupation = ?, 
                        emergency_contact_name = ?, emergency_contact_phone = ?, 
                        next_of_kin = ?, relationship_to_kin = ?, photo = ?, 
                        membership_date = ?, membership_fee_paid = ?, status = ?, notes = ?, 
                        updated_at = CURRENT_TIMESTAMP
                     WHERE id = ? AND chama_group_id = ?",
                    [
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
                        $formData['status'],
                        $formData['notes'] ?: null,
                        $memberId,
                        $chamaGroupId
                    ]
                );
                
                $db->commit();
                
                // Update member array with new data
                $member = array_merge($member, $formData);
                $member['photo'] = $photoPath;
                
                $success = "Member details updated successfully";
                
                // Redirect to member view page
                redirect("view.php?id=$memberId", $success);
                
            } catch (Exception $e) {
                $db->rollback();
                $error = $e->getMessage();
                logError("Failed to update member: " . $e->getMessage());
            }
        } else {
            $error = implode(', ', $errors);
        }
    }
}

