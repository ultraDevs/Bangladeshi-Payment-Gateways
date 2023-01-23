/**
 * All Admin Javascript Code here
 *
 * Javascript code will be written here
 *
 * @package BDPaymentsGateway
 */

jQuery(document).ready(
    function ($) {

        $(document).on(
            'click', '.add_qr_c_img', function (e) {
                var id = $(this).data('target');
                var qr = $(this).data('qr');
                var image = wp.media(
                    {
                        title: 'Upload Image',
                        multiple: false,
                    }
                ).open().on(
                    'select', function (e) {
                        var uploaded_img = image.state().get('selection').first();
                        var img_url = uploaded_img.toJSON().url;
                        $(id).val(img_url);
                        $(qr).html('<img src="' + img_url + '" alt="QR Code" />');
                        $('.add_qr_c_img').val('Edit Image');
                    }
                );

            }
        );

    }
);