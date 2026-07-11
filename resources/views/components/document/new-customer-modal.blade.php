{{--
    x-document.new-customer-modal
    The "New Customer" AJAX modal shared by create and edit forms.
    Requires customerPickerJs() to be loaded on the page.
--}}
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-errors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label><input type="text" id="m_name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label fw-medium">Company</label><input type="text" id="m_company" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-medium">Department</label><input type="text" id="m_department" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-medium">VAT Number</label><input type="text" id="m_vat_number" class="form-control"></div>
                    <div class="col-12"><label class="form-label fw-medium">Street</label><input type="text" id="m_street" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label fw-medium">City</label><input type="text" id="m_city" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label fw-medium">ZIP</label><input type="text" id="m_zip" class="form-control"></div>
                    <div class="col-md-5"><label class="form-label fw-medium">Country</label><input type="text" id="m_country" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-medium">Phone</label><input type="tel" id="m_phone" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-medium">Email</label><input type="email" id="m_email" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveNewCustomer">
                    <i class="bi bi-floppy me-1"></i>Save & Select
                </button>
            </div>
        </div>
    </div>
</div>
