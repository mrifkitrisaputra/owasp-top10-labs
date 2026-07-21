<?php
/**
 * Nac News Portal - Admin Vault (Final Flag)
 * 
 * FLAG 11: knowledge_is_the_true_eternal_flame
 * 
 * This page requires admin role, which can only be obtained through
 * cookie forgery using the COOKIE_SECRET found in config.php
 * (accessible via LFI or file upload shell).
 * 
 * Players must forge a remember_me cookie with role=admin to access this.
 */
$pageTitle = 'The Vault';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth('admin');

$conn = getDbConnection();

// The final secret
$vaultSecret = 'knowledge_is_the_true_eternal_flame';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="dashboard admin-dashboard">
        <div class="dashboard-sidebar">
            <h3><i class="fas fa-crown" style="color:gold;"></i> Admin Panel</h3>
            <ul class="dashboard-nav">
                <li><a href="/admin/"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="/admin/diagnostics.php"><i class="fas fa-stethoscope"></i> Diagnostics</a></li>
                <li><a href="/admin/vault.php" class="active"><i class="fas fa-vault"></i> Vault</a></li>
                <li><a href="/dashboard/"><i class="fas fa-edit"></i> Editor Panel</a></li>
                <li><a href="/"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            </ul>
        </div>

        <div class="dashboard-body">
            <div style="text-align:center;padding:40px 20px;">
                <!-- Vault Header -->
                <div style="margin-bottom:40px;">
                    <i class="fas fa-landmark" style="font-size:64px;color:#c9a227;margin-bottom:20px;display:block;"></i>
                    <h1 style="font-size:2.2em;color:#1a1a2e;margin-bottom:10px;">The Vault of Nac</h1>
                    <p style="font-size:1.1em;color:#666;max-width:600px;margin:0 auto;">
                        You have reached the innermost sanctum of the Nac News Portal. 
                        This vault contains the most guarded secret of our organization.
                    </p>
                </div>

                <!-- The Secret -->
                <div style="background:linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
                            color:#fff;padding:40px;border-radius:16px;max-width:700px;margin:0 auto 40px;
                            box-shadow:0 10px 40px rgba(0,0,0,0.3);">
                    
                    <div style="margin-bottom:25px;">
                        <i class="fas fa-scroll" style="font-size:36px;color:#c9a227;"></i>
                    </div>
                    
                    <h2 style="color:#c9a227;margin-bottom:15px;font-family:Georgia,serif;">
                        The Final Directive
                    </h2>
                    
                    <p style="color:rgba(255,255,255,0.7);margin-bottom:25px;font-size:14px;">
                        "In the words of the ancient scholars who built the Great Library, 
                        they inscribed upon its walls the ultimate truth:"
                    </p>

                    <div style="background:rgba(201,162,39,0.15);border:2px solid #c9a227;
                                border-radius:12px;padding:25px;margin-bottom:20px;">
                        <p style="font-family:'Georgia',serif;font-size:1.5em;color:#c9a227;
                                  letter-spacing:2px;margin:0;font-weight:bold;">
                            <?= $vaultSecret ?>
                        </p>
                    </div>

                    <p style="color:rgba(255,255,255,0.5);font-size:13px;margin:0;">
                        Congratulations. You have uncovered all the secrets of Nac.
                    </p>
                </div>

                <!-- Achievement -->
                <div style="background:#f8f9fa;border-radius:12px;padding:30px;max-width:700px;margin:0 auto;">
                    <h3 style="color:#1a1a2e;margin-bottom:15px;">
                        <i class="fas fa-trophy" style="color:#c9a227;"></i> Achievement Unlocked
                    </h3>
                    <p style="color:#666;margin-bottom:20px;">
                        You have demonstrated mastery over multiple security domains. 
                        The path that led you here required:
                    </p>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;text-align:left;">
                        <div style="padding:10px;background:#fff;border-radius:6px;border-left:3px solid #c9a227;">
                            <small style="color:#999;">Step 1</small><br>
                            <strong>Source Code Analysis</strong>
                        </div>
                        <div style="padding:10px;background:#fff;border-radius:6px;border-left:3px solid #c9a227;">
                            <small style="color:#999;">Step 2</small><br>
                            <strong>Cookie Secret Extraction</strong>
                        </div>
                        <div style="padding:10px;background:#fff;border-radius:6px;border-left:3px solid #c9a227;">
                            <small style="color:#999;">Step 3</small><br>
                            <strong>Authentication Bypass</strong>
                        </div>
                        <div style="padding:10px;background:#fff;border-radius:6px;border-left:3px solid #c9a227;">
                            <small style="color:#999;">Step 4</small><br>
                            <strong>Privilege Escalation</strong>
                        </div>
                    </div>
                </div>

                <!-- Admin Info -->
                <div style="margin-top:30px;color:#999;font-size:13px;">
                    <p>Authenticated as: <strong><?= e($_SESSION['username'] ?? 'Unknown') ?></strong> 
                       (Role: <?= e($_SESSION['role'] ?? 'Unknown') ?>)</p>
                    <p>Access Time: <?= date('Y-m-d H:i:s T') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
