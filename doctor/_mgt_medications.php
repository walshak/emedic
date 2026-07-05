<div class="row">

<div class="col-sm-6">
    <div class="form_sep">
        <label for="reg_select" class="">Filter By Form</label>
        <select name="drug_form" id="drug_form" class="form-control" v-model="drug_form" @change="filterByDosageForm">
            <option selected="selected" value="" style="font-size:14px"></option>
            <?php
            foreach ($DrugStock->distinctDosageForm() as $key => $dosage_arr) {
                if (!empty($dosage_arr->dosage)) {
            ?>
                    <option value="<?= $dosage_arr->dosage; ?>"><?= $dosage_arr->dosage; ?></option>
            <?php
                }
            }
            ?>
        </select>
    </div>

</div>

<div class="col-sm-6" style="color:#F00">

</div>



</div>
<hr>

<div class="row">
<div class="col-md-8">
    <table class="table table-striped table-bordered table-hover dataTables-example">
        <thead>
            <tr>
                <th data-toggle="true" width="1%">#</th>
                <th data-toggle="true" width="20%"><strong style="color:#F00"><i>Select Drug Name</i></strong></th>
                <th data-toggle="true">Hospital/<br>Price</th>
                <th data-toggle="true">Routine</th>
                <th data-toggle="true">Times</th>
                <th data-toggle="true"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(drug, index) in filterDrugStocks()" :key="index">
                <td>{{index + 1}}</td>
                <td>{{drug.product_name}}</td>
                <td>{{drug.hosp_price}}</td>
                <td>
                    <select name="frequency" :id="'med_frequency_'+drug.sn" class="form-control">
                        
                        <option value="BID">BID</option>
                        <option value="OD">OD</option>
                        <option value="TID">TID</option>
                        <option value="NOCTE">NOCTE</option>
                        <option value="QID">QID</option>
                    </select>
                </td>
                <td>
                    <select name="med_times" :id="'med_times_'+drug.sn" class="form-control">
                        <option value="">Select Times</option>
                        <option value="1 x 1">1 x 1</option>
                        <option value="1 x 2">1 x 2</option>
                        <option value="1 x 3">1 x 3</option>
                        <option value="2 x 1">2 x 1</option>
                        <option value="2 x 2">2 x 2</option>
                        <option value="2 x 3">2 x 3</option>
                        <option value="3 x 1">3 x 1</option>
                        <option value="3 x 2">3 x 2</option>
                        <option value="3 x 3">3 x 3</option>
                    </select>
                </td>
                <td><button class="btn btn-sm btn-success" @click="addDrug(drug)"><i class="fa fa-plus"></i></button></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="col-md-4">

    <div v-if="getSelectedDrugs().length > 0">
        <table class="table table-striped table-bordered table-hover dataTables-example">
            <thead>
                <tr>
                    <th data-toggle="true" width="1%">#</th>
                    <th data-toggle="true" width=""><strong style="color:#F00"><i>Selected Drug Names</i></strong></th>
                    <th data-toggle="true"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(drug, index) in getSelectedDrugs()" :key="index">
                    <td>{{index + 1}}</td>
                    <td>
                        {{drug.product_name}}
                        <br>
                        [{{drug.times+' '+drug.frequency}}]

                    </td>
                    <td><button class="btn btn-sm btn-danger"><i class="fa fa-minus" @click="removeDrug(index)"></i></button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>
