<div class="sidebard-panel">

    <br>
    <div class="m-t-md">
        <h4>Quick &amp; summary</h4>
        <div>
            <ul class="list-group">

                

                <li class="list-group-item ">
                    <span class="badge badge-info"> <?= $Transplant->count(); ?></span>
                    All transplant 
                </li>
                <li class="list-group-item ">
                    <span class="badge badge-info">
                        <?php
                        $stmt = $db->prepare("SELECT id from transplants_donors");
                        $stmt->execute();
                        echo  $stmt->rowCount();
                        ?>
                    </span>
                    No. of donors
                </li>
            </ul>

        </div>

      

    </div>
</div>