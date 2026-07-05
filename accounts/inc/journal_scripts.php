<link href="inc/select2.min.css" rel="stylesheet" />
<script src="inc/select2.min.js"></script>
<script>
    function get_totals() {
        let total_dr = 0;
        let total_cr = 0;
        $('.amount_dr').each(function(ind, ele) {
            total_dr = total_dr + parseFloat($(ele).val());
        });

        $('.amount_cr').each(function(ind, ele) {
            total_cr = total_cr + parseFloat($(ele).val());
        });

        $('#total_dr').text(total_dr);
        $('#total_cr').text(total_cr);
        return {
            total_dr,
            total_cr
        };
    }

    function addJournalRow() {
        let last_narration = $('.narrations:last').val();
        let markup =
            `
        <tr>
            <td>
                <button type="button" class="btn btn-danger" onclick="removeJournalRow(this)"><i class="fa fa-times"></i></button>
            </td>
            <td>	
<select name="account[]" class="form-control select2 account" required>
              
                    <?php foreach ($classes as $class) { ?>
						<option value="">-- select--</option>
                        <optgroup label="<?php echo $class['class_name']; ?>">
                            <?php
                            $cid = $class['cid'];
                            $groups = $db->prepare('SELECT * FROM chart_groups WHERE class_id = ? AND inactive = ?');
                            $groups->execute([$cid, 0]);
                            $groups = $groups->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php foreach ($groups as $group) { ?>
                                <optgroup label="<?php echo $group['name']; ?>">
                                    <?php
                                    $group_id = $group['id'];

                                    $accounts = $db->prepare('SELECT * FROM chart_accounts WHERE account_group = ? AND inactive = ?');
                                    $accounts->execute([$group_id, 0]);
                                    $accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <?php foreach ($accounts as $account) { ?>
                                        <option value="<?php echo $account['account_code']; ?>">[<?php echo $account['account_code']; ?>] <?php echo $account['account_name']; ?></option>
                                    <?php } ?>
                                </optgroup>
                            <?php } ?>
                        </optgroup>
                <?php } ?>
                </select>
            </td>
            <td>
                <input type="number" step="any" min="0" value="0" onkeyup="get_totals()" class="form-control amount_dr" name="amount_dr[]" required style="font-size: 20px;">
            </td>
            <td>
                <input type="number" step="any" min="0" value="0" onkeyup="get_totals()" class="form-control amount_cr" name="amount_cr[]" required style="font-size: 20px;">
            </td>
            <td>
                <textarea name="narration[]" class="from-control narrations" style="height: 35px; " cols="50">${last_narration}</textarea>
            </td>

        </tr>
            
        `;

        $('#journal_lines').append(markup);
        get_totals();
        $('.select2').select2();
    }

    function removeJournalRow(obj) {
        $(obj).closest('tr').remove();
        get_totals();
    }

    function do_submit(event, from_obj) {
        event.preventDefault()
        let count_dr_lines = 0;
        let count_cr_lines = 0;
        let err = '';
        let {
            total_dr,
            total_cr
        } = get_totals();
        $('.amount_dr').each(function(ind, ele) {
            if (($(ele).val()) != '' && (($(ele).val()) > 0)) {
                count_dr_lines++;
            }
        });
        $('.amount_cr').each(function(ind, ele) {
            if (($(ele).val()) != '' && (($(ele).val()) > 0)) {
                count_cr_lines++;
            }
        });

        if (count_cr_lines < 1) {
            err += 'You must have at least one row with CR amount grater than 0 <br>';
        }
        if (count_dr_lines < 1) {
            err += 'You must have at least one row with DR amount grater than 0 <br>';
        }

        if (total_cr != total_dr) {
            err += 'All CR amounts must be equal to DR amounts <br>';
        }

        if (err != '') {
            $('#gl_lines_errr').html(err);
        } else {
            // alert('hfhfhf');
            from_obj.submit();
        }
        console.log(count_dr_lines);
        console.log(count_cr_lines);
        console.log(total_dr);
        console.log(total_cr);
    }
</script>

<?php if (isset($_GET['ref'])) : ?>
    <script>
        get_totals();
    </script>
<?php endif ?>