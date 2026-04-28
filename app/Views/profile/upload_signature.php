<?php
$base = rtrim(BASE_URL, '/');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['account_id'])) {
    header('Location: ' . $base . '/login');
    exit;
}

// Determine user's dashboard route based on usertype
$usertype = strtolower($_SESSION['usertype'] ?? 'employee');
$dashboardRoute = match($usertype) {
    'admin' => '/admin',
    'it' => '/it',
    'head' => '/head',
    'employee' => '/employee',
    default => '/employee'
};
$dashboardUrl = htmlspecialchars($base . $dashboardRoute);
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Upload Signature - Storage Mart TMS</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>
<body id="page-top">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-pen"></i> Upload Your Signature</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Upload a clear signature image to be used on technical reports.</p>
                        
                        <form id="signatureForm" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="signatureFile" class="font-weight-bold">Signature Image</label>
                                <input type="file" class="form-control" id="signatureFile" name="signature" 
                                       accept="image/png,image/jpeg" required>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> PNG or JPG only | Max 5MB | Recommended: white background
                                </small>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Preview:</label>
                                <div class="border rounded p-3 bg-light" style="min-height: 150px;">
                                    <img id="preview" src="#" alt="Preview" style="max-width: 100%; max-height: 150px; display: none;">
                                    <p id="noPreview" class="text-muted mb-0">No image selected</p>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-upload"></i> Upload Signature
                            </button>
                            <a href="<?= $dashboardUrl ?>" class="btn btn-secondary btn-block mt-2">
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
    $(document).ready(function() {
        // Preview image on selection
        $('#signatureFile').change(function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview').attr('src', e.target.result).show();
                    $('#noPreview').hide();
                };
                reader.readAsDataURL(file);
            }
        });

        // Submit form
        $('#signatureForm').submit(function(e) {
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
                success: function(response) {
                    $('#message').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> Signature uploaded successfully!</div>');
                    setTimeout(() => window.location.href = '<?= $dashboardUrl ?>', 2000);
                },
                error: function(xhr) {
                    var error = 'Upload failed. Please try again.';
                    try {
                        error = JSON.parse(xhr.responseText).message || error;
                    } catch(e) {
                        error = xhr.responseText || error;
                    }
                    $('#message').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + error + '</div>');
                    submitBtn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload Signature');
                }
            });
        });
    });
    </script>
</body>
</html>
