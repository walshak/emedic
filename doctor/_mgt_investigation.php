<br>
                           
                            <br>
                            <hr>
                            <label for="reg_select" class="">Sort By Category</label> 
                            <select name="" id=""  v-model="lab_cat" @change="filterLabtestsByCategories">
                                <option value="">All</option>
                                <option value="Laboratory">Laboratory</option>
                                <option value="Radiology">Radiology</option>
                            </select>
                            <div class="row">
                                <div class="col-md-7">
                                    <table class="table table-striped table-bordered table-hover dataTables-example">
                                        <thead>
                                            <tr>
                                                <th width="3%">#</th>
                                                <th width="10%">NAME</th>
                                                <th width="10%">CATEGORY</th>
                                                <th width="7%">Specimen</th>
                                                <th width="7%">Note</th>
                                                <th width="7%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <tr v-for="(lab,index) in filter_labtests" :key="index">
                                                <td>{{index+1}}</td>
                                                <td>{{lab.test}}</td>
                                                <td>{{lab.category}}</td>
                                                <td>
                                                    <select name="specimen" data-placeholder="Select.." class="form-control" data-required="true" :id="'lab_specimen_'+lab.sn">
                                                        <option value="">Select</option>
                                                        <option value="No Specimen Required">No Specimen Required</option>
                                                        <option value="Aspirate">Aspirate</option>
                                                        <option value="Urine">Urine</option>
                                                        <option value="Blood">Blood</option>
                                                        <option value="C.S.F">C.S.F</option>
                                                        <option value="Ear Swab">Ear Swab</option>
                                                        <option value="Eye Swab">Eye Swab</option>
                                                        <option value="Fluids">Fluids</option>
                                                        <option value="Pap Smear">Pap Smear</option>
                                                        <option value="Semen">Semen</option>
                                                        <option value="Skin Scraping">Skin Scraping</option>
                                                        <option value="Sputum">Sputum</option>
                                                        <option value="Stool">Stool</option>
                                                        <option value="Throat Swab">Throat Swab</option>
                                                        <option value="Tissue">Tissue</option>
                                                        <option value="Urethral Swab">Urethral Swab</option>
                                                        <option value="Bence Jones Protein (Urine)">Bence Jones Protein (Urine)</option>
                                                        <option value="Viginal Swab">Viginal Swab</option>
                                                        <option value="Wound Swab">Wound Swab</option>

                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="request_note" placeholder="Request Note" :id="'lab_request_note_'+lab.sn">
                                                </td>
                                                <td><button class="btn btn-sm btn-success" @click="addLabTest(lab)"><i class="fa fa-plus"></i></button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-5">
                                    <div v-if="selected_labtests.length > 0" style="padding-left: 20px;">
                                        <h5>Selected:</h5>
                                        <table class="table table-striped table-bordered table-hover dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th width="3%">#</th>
                                                    <th width="10%">NAME</th>
                                                    <th width="10%">CATEGORY</th>
                                                    <th width="7%"></th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <tr v-for="(lab,index) in getSelectedLabTests()" :key="index">
                                                    <td>{{index+1}}</td>
                                                    <td>{{lab.test}}
                                                        <br>
                                                        Specimen:[{{lab.specimen}}]
                                                        <br>
                                                        Note:[{{lab.note}}]

                                                    </td>
                                                    <td>{{lab.category}}</td>

                                                    <td><button class="btn btn-sm btn-danger" @click="removeLabTest(index)"><i class="fa fa-minus"></i></button></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>