// Initialize admin panel
document.addEventListener('DOMContentLoaded', function() {
    loadAdminStats();
    loadAdminLogs();
});

// Load admin statistics
async function loadAdminStats() {
    try {
        const response = await fetch(`${API_URL}/admin.php?action=stats`, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.success) {
            document.getElementById('totalBooks').textContent = data.data.total_books;
            document.getElementById('totalUsers').textContent = data.data.total_users;
            document.getElementById('activeBorrowings').textContent = data.data.active_borrowings;
            document.getElementById('overdueBooks').textContent = data.data.overdue_books;
        } else {
            console.error('Failed to load stats:', data.message);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Show tab
function showTab(tabName) {
    // Hide all tabs
    document.getElementById('logsTab').style.display = 'none';
    document.getElementById('archiveTab').style.display = 'none';
    document.getElementById('configTab').style.display = 'none';

    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(btn => btn.classList.remove('active'));

    // Show selected tab
    if (tabName === 'logs') {
        document.getElementById('logsTab').style.display = 'block';
        event.target.classList.add('active');
        loadAdminLogs();
    } else if (tabName === 'archive') {
        document.getElementById('archiveTab').style.display = 'block';
        event.target.classList.add('active');
        loadArchiveList();
    } else if (tabName === 'config') {
        document.getElementById('configTab').style.display = 'block';
        event.target.classList.add('active');
        loadSystemConfig();
    }
}

// Load admin logs
async function loadAdminLogs() {
    try {
        const response = await fetch(`${API_URL}/admin.php?action=logs`, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.success) {
            displayAdminLogs(data.data);
            
            // Tampilkan meta information sebagai hint untuk IDOR
            if (data.meta) {
                const metaInfo = document.getElementById('logsMetaInfo');
                if (metaInfo) {
                    metaInfo.innerHTML = `
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <span>Showing ${data.meta.showing} of ${data.meta.total_in_system} total logs in system</span>
                            <small>${data.meta.note}</small>
                        </div>
                    `;
                }
            }
        } else {
            document.getElementById('adminLogs').innerHTML = `<p>${data.message}</p>`;
        }
    } catch (error) {
        console.error('Error loading logs:', error);
    }
}

// Display admin logs
function displayAdminLogs(logs) {
    const container = document.getElementById('adminLogs');

    if (logs.length === 0) {
        container.innerHTML = '<p>No logs found.</p>';
        return;
    }

    container.innerHTML = logs.map(log => `
        <div class="log-item">
            <h4>Log ID: ${log.id} - ${log.action}</h4>
            <p><strong>User:</strong> ${log.username || 'Unknown'} ${log.admin_id ? `(ID: ${log.admin_id})` : ''}</p>
            <p><strong>Details:</strong> ${log.details}</p>
            <p><strong>IP Address:</strong> ${log.ip_address}</p>
            <p><strong>Date:</strong> ${log.log_date}</p>
        </div>
    `).join('');
}

// FLAG 9: Search log by ID (IDOR vulnerability)
async function searchLogById() {
    const logId = document.getElementById('logIdSearch').value;

    if (!logId) {
        alert('Please enter a log ID');
        return;
    }

    try {
        const response = await fetch(`${API_URL}/admin.php?action=logs&log_id=${logId}`, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.success) {
            displayAdminLogs([data.data]);
            
            // Hint: Log yang berbeda dengan yang ditampilkan di list
            if (data.data.admin_id != document.getElementById('adminLogs').dataset.currentUserId) {
                console.log('Interesting... this log belongs to a different admin');
            }
        } else {
            document.getElementById('adminLogs').innerHTML = `<p>${data.message}</p>`;
        }
    } catch (error) {
        console.error('Error searching log:', error);
    }
}

// Load archive list
async function loadArchiveList() {
    try {
        const response = await fetch(`${API_URL}/admin.php?action=archive`, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.success) {
            displayArchiveList(data.data);
        } else {
            document.getElementById('archiveList').innerHTML = `<p>${data.message}</p>`;
        }
    } catch (error) {
        console.error('Error loading archive:', error);
    }
}

// Display archive list
function displayArchiveList(archives) {
    const container = document.getElementById('archiveList');
    container.innerHTML = archives.map(archive => `
        <div class="archive-item">
            <h4>${archive.title}</h4>
            <p><strong>Code:</strong> ${archive.archive_code}</p>
            <p><strong>Location:</strong> ${archive.storage_location}</p>
            <p><strong>Notes:</strong> ${archive.notes}</p>
            <p><small>Archived on: ${archive.archived_date}</small></p>
        </div>
    `).join('');
}

// FLAG 6: Search archive by code
async function searchArchiveByCode() {
    const code = document.getElementById('archiveCodeSearch').value;

    if (!code) {
        alert('Please enter an archive code');
        return;
    }

    try {
        const response = await fetch(`${API_URL}/admin.php?action=archive&code=${encodeURIComponent(code)}`, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.success) {
            displayArchiveList([data.data]);
        } else {
            document.getElementById('archiveList').innerHTML = `<p>${data.message}</p>`;
        }
    } catch (error) {
        console.error('Error searching archive:', error);
    }
}

// FLAG 9: Load system configuration (Privilege Escalation)
async function loadSystemConfig() {
    const showHidden = document.getElementById('showHidden').checked;

    try {
        const url = `${API_URL}/admin.php?action=config${showHidden ? '&show_hidden=true' : ''}`;
        const response = await fetch(url, {
            credentials: 'include'
        });
        const data = await response.json();

        if (data.success) {
            displaySystemConfig(data.data);
            
            // Hint: Log saat hidden configs diakses
            if (showHidden) {
                console.log('🔓 Hidden configurations revealed! You may have escalated your privileges...');
            }
        } else {
            document.getElementById('configList').innerHTML = `<p>${data.message}</p>`;
        }
    } catch (error) {
        console.error('Error loading config:', error);
    }
}

// Display system configuration
function displaySystemConfig(configs) {
    const container = document.getElementById('configList');

    if (configs.length === 0) {
        container.innerHTML = '<p>No configurations found.</p>';
        return;
    }

    // Separate visible and hidden configs
    const visibleConfigs = configs.filter(c => !c.is_hidden);
    const hiddenConfigs = configs.filter(c => c.is_hidden);

    let html = '';
    
    // Show count info
    html += `<div class="config-info" style="background: #e3f2fd; padding: 12px; border-radius: 4px; margin-bottom: 15px;">
        <i class="fas fa-info-circle"></i> 
        Showing ${configs.length} configuration(s)
        ${hiddenConfigs.length > 0 ? `<span style="color: #d32f2f; font-weight: bold;"> (${hiddenConfigs.length} hidden config revealed!)</span>` : ''}
    </div>`;

    // Display configs
    html += configs.map(config => {
        const isHidden = config.is_hidden == 1;
        const isMasterKey = config.config_key === 'master_key';
        
        return `
        <div class="config-item ${isHidden ? 'hidden-config' : ''} ${isMasterKey ? 'flag-config' : ''}" style="${isMasterKey ? 'border-left: 4px solid #4caf50; background: #f1f8e9;' : ''}">
            <h4>
                ${config.config_key} 
                ${isHidden ? '<span style="color: red; font-size: 0.9em;">(🔒 Hidden)</span>' : ''}
                ${isMasterKey ? '<span style="color: green; font-size: 0.9em;">🎯</span>' : ''}
            </h4>
            <p><strong>Value:</strong> <code style="background: #f5f5f5; padding: 4px 8px; border-radius: 3px; font-family: monospace;">${config.config_value}</code></p>
            ${config.description ? `<p><strong>Description:</strong> ${config.description}</p>` : ''}
            <p style="font-size: 0.85em; color: #666;"><strong>Last Updated:</strong> ${config.updated_at}</p>
        </div>
    `;
    }).join('');

    container.innerHTML = html;
}
