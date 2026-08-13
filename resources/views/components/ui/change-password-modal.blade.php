{{--
    x-ui.change-password-modal
    Modal allowing the logged-in user to change their password
    (current password + new password + confirmation).
--}}
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @if ($errors->any())
                        <x-ui.alert type="danger" :dismissible="false" :small="true">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            {{ $errors->first() }}
                        </x-ui.alert>
                    @endif

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-medium small">Current password</label>
                        <input type="password" id="current_password" name="current_password"
                            class="form-control form-control-sm @error('current_password') is-invalid @enderror"
                            required autocomplete="current-password">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium small">New password</label>
                        <input type="password" id="password" name="password"
                            class="form-control form-control-sm @error('password') is-invalid @enderror"
                            required minlength="8" autocomplete="new-password">
                    </div>

                    <div class="mb-1">
                        <label for="password_confirmation" class="form-label fw-medium small">Confirm new password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control form-control-sm" required minlength="8" autocomplete="new-password">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-key me-1"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
