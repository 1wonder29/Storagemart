<?php
$base = rtrim(BASE_URL, '/');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['account_id'])) {
    header('Location: ' . $base . '/login');
    exit;
}

$usertype = strtolower($_SESSION['usertype'] ?? 'employee');
$dashboardRoute = match ($usertype) {
    'admin' => '/admin',
    'it' => '/it',
    'head' => '/head',
    'employee' => '/employee',
    default => '/employee',
};
$dashboardUrl = htmlspecialchars($base . $dashboardRoute);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Upload Signature - Storage Mart TMS</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/auth-login.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/ui-readonly-interaction.css?v=2" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-form-page.css" rel="stylesheet">
</head>
<body class="auth-page signature-upload-page">

<div class="auth-ambient" aria-hidden="true"></div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="text-center mb-4">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/storagemart-logo.png" alt="Storage Mart" class="signature-upload-logo mb-3">
                <h1 class="h4 text-white font-weight-bold mb-1">Upload Your Signature</h1>
                <p class="text-white-50 mb-0">Used on technical reports and accountability forms.</p>
            </div>

            <div class="card signature-upload-card shadow-lg border-0">
                <div class="card-body p-4">
                    <form id="signatureForm" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="signatureFile" class="font-weight-bold">Signature Image</label>
                            <input type="file" class="form-control" id="signatureFile" name="signature"
                                   accept="image/png,image/jpeg" required>
                            <small class="form-text text-muted">
                                PNG or JPG only · Max 5MB · White background recommended
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Preview</label>
                            <div class="signature-preview-box border rounded p-3">
                                <img id="preview" src="#" alt="Preview" class="signature-preview-img">
                                <p id="noPreview" class="text-muted mb-0">No image selected</p>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block signature-upload-btn">
                            <i class="fas fa-upload mr-1"></i> Upload Signature
                        </button>
                        <a href="<?= $dashboardUrl ?>" class="btn btn-outline-secondary btn-block mt-2">
                            Cancel
                        </a>
                    </form>

                    <div id="message" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function () {
    $('#signatureFile').change(function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#preview').attr('src', e.target.result).show();
                $('#noPreview').hide();
            };
            reader.readAsDataURL(file);
        }
    });

    $('#signatureForm').submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        var submitBtn = $(this).find('button[type="submit"]');

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');

        $.ajax({
            url: '<?= htmlspecialchars($base) ?>/profile/upload-signature',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                $('#message').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> Signature uploaded successfully!</div>');
                setTimeout(function () { window.location.href = '<?= $dashboardUrl ?>'; }, 2000);
            },
            error: function (xhr) {
                var error = 'Upload failed. Please try again.';
                try {
                    error = JSON.parse(xhr.responseText).message || error;
                } catch (err) {
                    error = xhr.responseText || error;
                }
                $('#message').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + error + '</div>');
                submitBtn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Upload Signature');
            }
        });
    });
});
</script>
</body>
</html>
