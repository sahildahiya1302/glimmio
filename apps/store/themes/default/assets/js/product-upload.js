document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('manual-product-form');
  const fileInput = document.getElementById('productFile');
  const imageUrlInput = document.getElementById('productImage');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    if (fileInput.files.length > 0) {
      const file = fileInput.files[0];
      const formData = new FormData();
      formData.append('product_file', file);

      try {
        const response = await fetch('/backend/utils/upload-image.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        if (result.success) {
          imageUrlInput.value = result.url;
        } else {
          alert('Image upload failed: ' + result.message);
          return;
        }
      } catch (error) {
        alert('Image upload error: ' + error.message);
        return;
      }
    }

    // Submit the form data as JSON or normal form submission here
    // For simplicity, submit the form normally
    form.submit();
  });
});
