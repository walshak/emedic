<?php
require_once(__DIR__ . '/../Connections/Conn.php');
require_once(__DIR__ . '/../inc/lis/LisService.php');

$message = '';
$message_type = 'info';

// Handle Global Config Save via POST
if (isset($_POST['save_lis_config'])) {
    $is_enabled = isset($_POST['is_enabled']) ? 1 : 0;
    $provider_driver = trim($_POST['provider_driver'] ?? 'clinos');
    $base_url = trim($_POST['base_url'] ?? '');
    $emr_key = trim($_POST['emr_key'] ?? '');
    $catalogue_key = trim($_POST['catalogue_key'] ?? '');

    try {
        $stmt = $db->prepare("UPDATE lis_config SET 
            is_enabled = :is_enabled,
            provider_driver = :provider_driver,
            base_url = :base_url,
            emr_key = :emr_key,
            catalogue_key = :catalogue_key,
            updated_at = NOW()
            WHERE id = 1");

        $stmt->execute([
            ':is_enabled' => $is_enabled,
            ':provider_driver' => $provider_driver,
            ':base_url' => $base_url,
            ':emr_key' => $emr_key,
            ':catalogue_key' => $catalogue_key
        ]);

        LisDriverFactory::getConfig($db, true); // Refresh cache
        $message = "LIS Configuration updated successfully.";
        $message_type = "success";
    } catch (Exception $e) {
        $message = "Error updating configuration: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Handle AJAX actions
if (isset($_REQUEST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_REQUEST['ajax_action'];

    try {
        if ($action === 'test_connection') {
            $driver = LisDriverFactory::getDriver($db);
            $catalogData = $driver->getTestCatalog();
            $items = $catalogData['tests'] ?? (is_array($catalogData) ? $catalogData : []);
            $count = count($items);
            echo json_encode(['status' => 'success', 'message' => "Connected successfully to ClinOS LIS! Catalogue contains {$count} active orderable tests."]);
            exit;
        }

        if ($action === 'fetch_catalog') {
            $driver = LisDriverFactory::getDriver($db);
            $catalogData = $driver->getTestCatalog();
            $items = $catalogData['tests'] ?? (is_array($catalogData) ? $catalogData : []);
            echo json_encode(['status' => 'success', 'catalog' => $items]);
            exit;
        }

        if ($action === 'save_mapping_ajax') {
            $lab_scan_id = (int)$_POST['lab_scan_id'];
            $emr_test_name = trim($_POST['emr_test_name']);
            $canonical_code = trim($_POST['canonical_code']);
            $is_active = isset($_POST['is_active']) && $_POST['is_active'] == 1 ? 1 : 0;

            $config = LisDriverFactory::getConfig($db);
            $provider = $config['provider_driver'] ?? 'clinos';

            if (empty($canonical_code)) {
                $delStmt = $db->prepare("DELETE FROM lis_test_mappings WHERE lab_scan_id = :lab_scan_id AND lis_provider = :provider");
                $delStmt->execute([':lab_scan_id' => $lab_scan_id, ':provider' => $provider]);
                echo json_encode(['status' => 'success', 'message' => 'Mapping removed', 'mapped' => false]);
                exit;
            } else {
                $insStmt = $db->prepare("INSERT INTO lis_test_mappings 
                    (lab_scan_id, emr_test_name, canonical_code, lis_provider, is_active)
                    VALUES
                    (:lab_scan_id, :emr_test_name, :canonical_code, :provider, :is_active)
                    ON DUPLICATE KEY UPDATE
                    canonical_code = VALUES(canonical_code),
                    is_active = VALUES(is_active)");

                $insStmt->execute([
                    ':lab_scan_id' => $lab_scan_id,
                    ':emr_test_name' => $emr_test_name,
                    ':canonical_code' => $canonical_code,
                    ':provider' => $provider,
                    ':is_active' => $is_active
                ]);

                echo json_encode(['status' => 'success', 'message' => "Mapped to {$canonical_code}", 'mapped' => true, 'canonical_code' => $canonical_code, 'is_active' => $is_active]);
                exit;
            }
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// Load Current Config
$config = LisDriverFactory::getConfig($db, true);
$is_enabled = $config['is_enabled'] ?? 0;
$provider_driver = $config['provider_driver'] ?? 'clinos';
$base_url = $config['base_url'] ?? 'https://zmkc-livs.shares.zrok.io';
$emr_key = $config['emr_key'] ?? '';
$catalogue_key = $config['catalogue_key'] ?? '';
$last_polled_after_id = $config['last_polled_after_id'] ?? 0;
$last_poll_timestamp = $config['last_poll_timestamp'] ?? 'Never';

// Load EMR Lab Tests and current mappings
$testSql = "SELECT s.sn, s.test, s.category, s.sub_category, m.canonical_code, m.is_active 
            FROM lab_scan s 
            LEFT JOIN lis_test_mappings m ON s.sn = m.lab_scan_id AND m.lis_provider = :provider 
            ORDER BY s.test ASC";
$testStmt = $db->prepare($testSql);
$testStmt->execute([':provider' => $provider_driver]);
$emrTests = $testStmt->fetchAll(PDO::FETCH_ASSOC);

$totalTests = count($emrTests);
$mappedCount = 0;
foreach ($emrTests as $t) {
    if (!empty($t['canonical_code']) && (!isset($t['is_active']) || $t['is_active'] == 1)) {
        $mappedCount++;
    }
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>External Laboratory Information System (LIS) Settings</h5>
                <div class="ibox-tools">
                    <span class="label <?php echo ($is_enabled ? 'label-primary' : 'label-danger'); ?>">
                        <i class="fa fa-power-off"></i> LIS <?php echo ($is_enabled ? 'ENABLED' : 'DISABLED'); ?>
                    </span>
                    &nbsp;
                    <span class="label label-info">
                        <i class="fa fa-link"></i> Mapped: <?php echo $mappedCount; ?> / <?php echo $totalTests; ?>
                    </span>
                </div>
            </div>
            <div class="ibox-content">

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div id="ajaxToastMsg" style="display:none;" class="alert alert-info alert-dismissible">
                    <button type="button" class="close" onclick="$('#ajaxToastMsg').hide();">&times;</button>
                    <span id="ajaxToastText"></span>
                </div>

                <div class="row">
                    <!-- Left Column: Credentials & Driver Settings -->
                    <div class="col-md-5">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <strong><i class="fa fa-cogs"></i> LIS Provider & Credentials</strong>
                            </div>
                            <div class="panel-body">
                                <form action="index.php?lis_settings" method="POST" id="lis_config_form">
                                    
                                    <div class="form-group">
                                        <label for="is_enabled" class="req">
                                            <input type="checkbox" id="is_enabled" name="is_enabled" value="1" <?php echo ($is_enabled == 1) ? 'checked' : ''; ?>>
                                            <strong>Enable External LIS Dispatch</strong>
                                        </label>
                                        <p class="help-block"><small>When enabled, lab requests with mapped tests will automatically send to LIS.</small></p>
                                    </div>

                                    <div class="form-group">
                                        <label for="provider_driver" class="req">LIS Provider / Driver</label>
                                        <select name="provider_driver" id="provider_driver" class="form-control" required>
                                            <option value="clinos" <?php echo ($provider_driver === 'clinos') ? 'selected' : ''; ?>>ClinOS (ZMKC LIVS API)</option>
                                        </select>
                                        <p class="help-block"><small>Swappable provider driver layer.</small></p>
                                    </div>

                                    <div class="form-group">
                                        <label for="base_url" class="req">LIS Base URL</label>
                                        <input type="url" id="base_url" name="base_url" class="form-control" value="<?php echo htmlspecialchars($base_url); ?>" required placeholder="https://zmkc-livs.shares.zrok.io">
                                    </div>

                                    <div class="form-group">
                                        <label for="emr_key">EMR Integration Key (<code>X-ClinOS-EMR-Key</code>)</label>
                                        <input type="password" id="emr_key" name="emr_key" class="form-control" value="<?php echo htmlspecialchars($emr_key); ?>" placeholder="Enter EMR API Key">
                                    </div>

                                    <div class="form-group">
                                        <label for="catalogue_key">Catalogue Integration Key (<code>X-ClinOS-Key</code>)</label>
                                        <input type="password" id="catalogue_key" name="catalogue_key" class="form-control" value="<?php echo htmlspecialchars($catalogue_key); ?>" placeholder="Enter Catalogue API Key">
                                    </div>

                                    <div class="well well-sm">
                                        <small>
                                            <strong>Last Polled Cursor:</strong> <code><?php echo $last_polled_after_id; ?></code><br>
                                            <strong>Last Poll Time:</strong> <?php echo htmlspecialchars($last_poll_timestamp); ?>
                                        </small>
                                    </div>

                                    <div class="form_sep m-t-md">
                                        <div class="pull-left">
                                            <button class="btn btn-success" type="submit" name="save_lis_config">
                                                <i class="fa fa-save"></i> Save Settings
                                            </button>
                                        </div>
                                        <div class="pull-right">
                                            <button type="button" id="btnTestConn" class="btn btn-info">
                                                <i class="fa fa-plug"></i> Test Connection
                                            </button>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Investigation Test Mapping Matrix -->
                    <div class="col-md-7">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <strong><i class="fa fa-exchange"></i> EMR Investigation Mapping Matrix</strong>
                                <button type="button" id="btnFetchCatalog" class="btn btn-xs btn-primary pull-right">
                                    <i class="fa fa-refresh"></i> Sync LIS Catalogue
                                </button>
                            </div>
                            <div class="panel-body">
                                
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                        <input type="text" id="searchMapping" class="form-control" placeholder="Search EMR test name or canonical code...">
                                    </div>
                                </div>

                                <datalist id="lisCatalogList"></datalist>

                                <div style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-striped table-bordered" id="mappingTable">
                                        <thead>
                                            <tr>
                                                <th>EMR Test Name</th>
                                                <th>LIS Canonical Code</th>
                                                <th style="width: 70px;" class="text-center">Active</th>
                                                <th style="width: 80px;" class="text-center">Manage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($emrTests as $t): 
                                                $isMapped = !empty($t['canonical_code']);
                                                $isActive = !isset($t['is_active']) || $t['is_active'] == 1;
                                            ?>
                                                <tr class="mapping-row" id="row_<?php echo $t['sn']; ?>" data-search="<?php echo htmlspecialchars(strtolower($t['test'] . ' ' . ($t['canonical_code'] ?? ''))); ?>">
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($t['test']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($t['sub_category'] ?: $t['category']); ?></small>
                                                        <br>
                                                        <span class="status-indicator badge <?php echo $isMapped ? 'badge-primary' : 'badge-danger'; ?>">
                                                            <?php echo $isMapped ? 'Mapped' : 'Unmapped'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control input-sm code-input" list="lisCatalogList" value="<?php echo htmlspecialchars($t['canonical_code'] ?? ''); ?>" placeholder="e.g. FBC, URINALYSIS" style="font-weight: bold;">
                                                    </td>
                                                    <td class="text-center" style="vertical-align: middle;">
                                                        <input type="checkbox" class="active-checkbox" <?php echo $isActive ? 'checked' : ''; ?>>
                                                    </td>
                                                    <td class="text-center" style="vertical-align: middle;">
                                                        <button type="button" class="btn btn-xs btn-success btn-save-row" data-sn="<?php echo $t['sn']; ?>" data-name="<?php echo htmlspecialchars($t['test']); ?>">
                                                            <i class="fa fa-check"></i> Save
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function showToast(msg, type) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    $('#ajaxToastMsg').removeClass('alert-info alert-success alert-danger alert-warning').addClass(alertClass);
    $('#ajaxToastText').html(msg);
    $('#ajaxToastMsg').show();
    setTimeout(function() { $('#ajaxToastMsg').fadeOut(); }, 4000);
}

$(document).ready(function() {
    // Live Search Filter for Test Mappings
    $('#searchMapping').on('keyup input', function() {
        var term = $(this).val().toLowerCase().trim();
        $('.mapping-row').each(function() {
            var searchData = $(this).data('search') || '';
            if (searchData.indexOf(term) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Test Connection Button
    $('#btnTestConn').click(function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing...');

        $.getJSON('lis_settings.php?ajax_action=test_connection', function(res) {
            $btn.prop('disabled', false).html('<i class="fa fa-plug"></i> Test Connection');
            if (res.status === 'success') {
                showToast(res.message, 'success');
            } else {
                showToast('Connection Error: ' + res.message, 'error');
            }
        }).fail(function(jqxhr, textStatus, error) {
            $btn.prop('disabled', false).html('<i class="fa fa-plug"></i> Test Connection');
            showToast('Failed to connect to LIS server: ' + (error || textStatus), 'error');
        });
    });

    // Sync LIS Catalogue
    $('#btnFetchCatalog').click(function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Syncing...');

        $.getJSON('lis_settings.php?ajax_action=fetch_catalog', function(res) {
            $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Sync LIS Catalogue');
            if (res.status === 'success' && Array.isArray(res.catalog)) {
                var optionsHtml = '';
                res.catalog.forEach(function(item) {
                    var code = item.canonical_code || item.code || '';
                    var name = item.display_name || item.name || code;
                    if (code) {
                        optionsHtml += '<option value="' + code + '">' + name + ' (' + code + ')</option>';
                    }
                });
                $('#lisCatalogList').html(optionsHtml);
                showToast('LIS Catalogue synced! ' + res.catalog.length + ' test codes loaded.', 'success');
            } else {
                showToast('Error syncing catalogue: ' + (res.message || 'Unknown error'), 'error');
            }
        }).fail(function(jqxhr, textStatus, error) {
            $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Sync LIS Catalogue');
            showToast('Failed to connect to LIS server: ' + (error || textStatus), 'error');
        });
    });

    // Inline AJAX Save for Individual Test Mappings
    $('.btn-save-row').click(function() {
        var $btn = $(this);
        var sn = $btn.data('sn');
        var emrName = $btn.data('name');
        var $row = $('#row_' + sn);
        var code = $row.find('.code-input').val().trim();
        var isActive = $row.find('.active-checkbox').is(':checked') ? 1 : 0;

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.post('lis_settings.php', {
            ajax_action: 'save_mapping_ajax',
            lab_scan_id: sn,
            emr_test_name: emrName,
            canonical_code: code,
            is_active: isActive
        }, function(res) {
            $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save');
            if (res.status === 'success') {
                showToast(res.message, 'success');
                var $indicator = $row.find('.status-indicator');
                if (res.mapped) {
                    $indicator.removeClass('badge-danger').addClass('badge-primary').text('Mapped');
                } else {
                    $indicator.removeClass('badge-primary').addClass('badge-danger').text('Unmapped');
                }
            } else {
                showToast('Save Failed: ' + res.message, 'error');
            }
        }, 'json').fail(function() {
            $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save');
            showToast('Network error while saving mapping.', 'error');
        });
    });
});
</script>
