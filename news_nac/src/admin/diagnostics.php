<?php
/**
 * Nac News Portal - Admin Diagnostics
 * 
 * VULNERABILITY: OS Command Injection (Flag 10)
 * 
 * The diagnostics tool runs ping/traceroute commands using shell_exec
 * with insufficient input sanitization. Players need to discover this
 * page via admin_actions.log (through LFI) which reveals the token.
 * 
 * Access requires: ?token=7f3a9b2c (found in /var/log/nac/admin_actions.log)
 * Flag: seek_the_vault_of_knowledge (in /opt/nac/final_directive.txt)
 */
$pageTitle = 'System Diagnostics';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Token-based access - does not require login but needs secret token
$validToken = '7f3a9b2c';
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if ($token !== $validToken) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><title>403 Forbidden</title></head>';
    echo '<body><h1>403 Forbidden</h1><p>Access denied. Valid diagnostic token required.</p>';
    echo '<p><small>Usage: diagnostics.php?token=YOUR_TOKEN</small></p></body></html>';
    exit;
}

$commandOutput = null;
$target = '';
$tool = 'ping';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = trim($_POST['target'] ?? '');
    $tool = $_POST['tool'] ?? 'ping';
    
    if (!empty($target)) {
        // "Sanitization" that's intentionally bypassable
        // Only blocks some obvious patterns but misses many
        $blocked = ['&&', '||', '`', '$(`', 'wget', 'curl', 'nc ', 'python', 'perl', 'ruby'];
        $isBlocked = false;
        
        foreach ($blocked as $pattern) {
            if (stripos($target, $pattern) !== false) {
                $isBlocked = true;
                break;
            }
        }
        
        if ($isBlocked) {
            $commandOutput = "Error: Input contains blocked characters or commands.";
        } else {
            // VULNERABILITY: Command injection via semicolons, pipes, etc.
            switch ($tool) {
                case 'ping':
                    $cmd = "ping -c 4 " . $target . " 2>&1";
                    break;
                case 'traceroute':
                    $cmd = "traceroute " . $target . " 2>&1";
                    break;
                case 'nslookup':
                    $cmd = "nslookup " . $target . " 2>&1";
                    break;
                case 'whois':
                    $cmd = "whois " . $target . " 2>&1";
                    break;
                default:
                    $cmd = "ping -c 4 " . $target . " 2>&1";
            }
            
            $commandOutput = shell_exec($cmd);
            
            if (empty($commandOutput)) {
                $commandOutput = "No output received. The command may have timed out.";
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="dashboard admin-dashboard">
        <div class="dashboard-sidebar">
            <h3><i class="fas fa-crown" style="color:gold;"></i> Admin Panel</h3>
            <ul class="dashboard-nav">
                <li><a href="/admin/?token=<?= e($token) ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="/admin/diagnostics.php?token=<?= e($token) ?>" class="active"><i class="fas fa-stethoscope"></i> Diagnostics</a></li>
                <li><a href="/admin/vault.php"><i class="fas fa-vault"></i> Vault</a></li>
                <li><a href="/dashboard/"><i class="fas fa-edit"></i> Editor Panel</a></li>
                <li><a href="/"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            </ul>
        </div>

        <div class="dashboard-body">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <div>
                    <h2><i class="fas fa-stethoscope"></i> System Diagnostics</h2>
                    <p class="text-muted">Network diagnostic tools for system administrators.</p>
                </div>
                <span class="status-badge status-published">Token Verified</span>
            </div>

            <!-- Diagnostic Tool Form -->
            <div style="background:#f8f9fa;padding:25px;border-radius:8px;margin-bottom:25px;">
                <form method="POST">
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    
                    <div style="display:grid;grid-template-columns:150px 1fr auto;gap:15px;align-items:end;">
                        <div class="form-group" style="margin:0;">
                            <label for="tool">Tool</label>
                            <select name="tool" id="tool" class="form-control">
                                <option value="ping" <?= $tool === 'ping' ? 'selected' : '' ?>>Ping</option>
                                <option value="traceroute" <?= $tool === 'traceroute' ? 'selected' : '' ?>>Traceroute</option>
                                <option value="nslookup" <?= $tool === 'nslookup' ? 'selected' : '' ?>>NSLookup</option>
                                <option value="whois" <?= $tool === 'whois' ? 'selected' : '' ?>>Whois</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin:0;">
                            <label for="target">Target Host / IP</label>
                            <input type="text" name="target" id="target" class="form-control" 
                                   placeholder="e.g., 8.8.8.8 or google.com" value="<?= e($target) ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="height:42px;">
                            <i class="fas fa-play"></i> Run
                        </button>
                    </div>
                </form>
            </div>

            <?php if ($commandOutput !== null): ?>
            <!-- Output -->
            <div class="diagnostic-output">
                <div style="background:#1a1a2e;color:#0f0;border-radius:8px;overflow:hidden;">
                    <div style="background:#0d0d1a;padding:10px 15px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#888;font-size:13px;">
                            <i class="fas fa-terminal"></i> Output — <?= e($tool) ?> <?= e($target) ?>
                        </span>
                        <span style="color:#666;font-size:12px;">nac-server</span>
                    </div>
                    <pre style="padding:20px;margin:0;color:#0f0;font-family:'Courier New',monospace;font-size:13px;line-height:1.6;overflow-x:auto;max-height:500px;"><?= e($commandOutput) ?></pre>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Tests -->
            <div style="margin-top:25px;">
                <h3 style="margin-bottom:15px;">Quick Tests</h3>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;">
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        <input type="hidden" name="tool" value="ping">
                        <input type="hidden" name="target" value="127.0.0.1">
                        <button type="submit" class="btn btn-outline" style="width:100%;text-align:left;">
                            <i class="fas fa-network-wired"></i> Ping Localhost
                        </button>
                    </form>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        <input type="hidden" name="tool" value="nslookup">
                        <input type="hidden" name="target" value="google.com">
                        <button type="submit" class="btn btn-outline" style="width:100%;text-align:left;">
                            <i class="fas fa-search"></i> DNS Check (Google)
                        </button>
                    </form>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        <input type="hidden" name="tool" value="ping">
                        <input type="hidden" name="target" value="internal-api">
                        <button type="submit" class="btn btn-outline" style="width:100%;text-align:left;">
                            <i class="fas fa-server"></i> Ping Internal API
                        </button>
                    </form>
                </div>
            </div>

            <!-- Warning -->
            <div style="margin-top:25px;padding:15px;background:#fff3cd;border:1px solid #ffc107;border-radius:8px;">
                <p style="margin:0;color:#856404;font-size:14px;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Security Notice:</strong> This diagnostic interface executes network commands on the server. 
                    Access is restricted to authorized administrators with valid tokens. 
                    Certain dangerous commands and characters are blocked for security.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
