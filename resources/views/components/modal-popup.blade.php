
@if (Route::is(['rental-payment']))
    <!-- Start Success Modal  -->
    <div class="modal fade" id="payment-success">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content payment">
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <span class="avatar avatar-lg bg-success rounded-circle text-white"><x-icon name="done_all" class="fs-24"/></span>
                    </div>
                    <h6 class="mb-2">Payment Successful</h6>
                    <p class="mb-2">You Payment has been successfully done.</p>
                    <p class="mb-4">Trasaction Id : #5064164454</p>
                    <div class="d-flex justify-content-center">
                        <a href="{{url('index')}}" class="btn btn-lg btn-dark">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Success Modal  -->
@endif

@if (Route::is(['checkout']))
	<!-- Start Add Modal -->
	<div id="add_card" class="modal fade">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form action="{{url('checkout')}}">
					<div class="modal-header">
						<h4 class="text-dark modal-title fw-bold">Add New Card</h4>
						<button type="button" class="btn-close btn-close-modal custom-btn-close" data-bs-dismiss="modal" aria-label="Close"><x-icon name="close"/></button>
					</div>
					<div class="modal-body">
						<div class="mb-3">
							<label class="form-label">Card Number<span class="text-danger ms-1">*</span></label>
							<input type="text" class="form-control">
						</div>
						<div class="mb-3">
							<label class="form-label">Expiration Date<span class="text-danger ms-1">*</span></label>
							<div class="input-group input-group-flat mb-3">
								<input type="text" class="datetimepicker form-control" placeholder="dd/mm/yyyy">
								<span class="input-group-text border-0">
									<x-icon name="calendar_today" class="text-dark"/>
								</span>
							</div>
						</div>
						<div class="mb-0">
							<label class="form-label">CVV<span class="text-danger ms-1">*</span></label>
							<input type="text" class="form-control">
						</div>
					</div>
					<div class="modal-body border-top">
						<div class="d-flex align-items-center justify-content-end">
							<button type="button" class="btn btn-lg btn-light me-2" data-bs-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-lg btn-primary">Add New Card</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- End Add Modal -->

	<!-- Start Add Modal -->
	<div id="edit_card" class="modal fade">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form action="{{url('checkout')}}">
					<div class="modal-header">
						<h4 class="text-dark modal-title fw-bold">Edit Card</h4>
						<button type="button" class="btn-close btn-close-modal custom-btn-close" data-bs-dismiss="modal" aria-label="Close"><x-icon name="close"/></button>
					</div>
					<div class="modal-body">
						<div class="mb-3">
							<label class="form-label">Card Number<span class="text-danger ms-1">*</span></label>
							<input type="text" class="form-control" value="1234 5678 9876 5432">
						</div>
						<div class="mb-3">
							<label class="form-label">Expiration Date<span class="text-danger ms-1">*</span></label>
							<div class="input-group input-group-flat mb-3">
								<input type="text" class="datetimepicker form-control" placeholder="dd/mm/yyyy">
								<span class="input-group-text border-0">
									<x-icon name="calendar_today" class="text-dark"/>
								</span>
							</div>
						</div>
						<div class="mb-0">
							<label class="form-label">CVV<span class="text-danger ms-1">*</span></label>
							<input type="text" class="form-control" value="645">
						</div>
					</div>
					<div class="modal-body border-top">
						<div class="d-flex align-items-center justify-content-end">
							<button type="button" class="btn btn-lg btn-light me-2" data-bs-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-lg btn-primary">Save Changes</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- End Add Modal -->

	<!-- Start Delete Modal  -->
	<div class="modal fade" id="delete_card">
		<div class="modal-dialog modal-dialog-centered modal-sm">
			<div class="modal-content">
				<div class="modal-body text-center">
					<div class="mb-3">
						<span class="avatar avatar-lg bg-danger rounded-circle text-white"><x-icon name="delete" class="fs-24"/></span>
					</div>
					<h6 class="mb-1">Delete Confirmation</h6>
					<p class="mb-3">Are you sure want to delete?</p>
					<div class="d-flex justify-content-center">
						<a href="javascript:void(0);" class="btn btn-light position-relative z-1 me-3" data-bs-dismiss="modal">Cancel</a>
						<a href="{{url('checkout')}}" class="btn btn-danger position-relative z-1">Yes, Delete</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Delete Modal  -->

	<!-- Start Success Modal  -->
	<div class="modal fade" id="payment-success">
		<div class="modal-dialog modal-dialog-centered modal-md">
			<div class="modal-content payment">
				<div class="modal-body text-center">
					<div class="mb-3">
						<span class="avatar avatar-lg bg-success rounded-circle text-white"><x-icon name="done_all" class="fs-24"/></span>
					</div>
					<h6 class="mb-2">Payment Successful</h6>
					<p class="mb-2">You Payment has been successfully done.</p>
					<p class="mb-4">Trasaction Id : #5064164454</p>
					<div class="d-flex justify-content-center">
						<a href="{{url('index')}}" class="btn btn-lg btn-dark">Back to Home</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Success Modal  -->
@endif
