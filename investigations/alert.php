<div class="modal fade" id="labModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4>New Lab / Radiology Requests</h4>
            </div>

            <div class="modal-body" style="max-height:400px; overflow:auto;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Test</th>
                            <th>Type</th>
                            <th>Requested By</th>
                            <th>Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="lab_body"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>