jQuery(function($) {

    var nextgen_lightbox_loading_img_url = '/wp-content/plugins/nextgen-gallery/products/photocrati_nextgen/modules/lightbox/static/images/lightbox-ico-loading.gif';
    var nextgen_lightbox_close_btn_url   = '/wp-content/plugins/nextgen-gallery/products/photocrati_nextgen/modules/lightbox/static/images/lightbox-btn-close.gif';
    var nextgen_lightbox_btn_prev_url = '/wp-content/plugins/nextgen-gallery/products/photocrati_nextgen/modules/lightbox/static/images/lightbox-btn-prev.gif';
    var nextgen_lightbox_btn_next_url = '/wp-content/plugins/nextgen-gallery/products/photocrati_nextgen/modules/lightbox/static/images/lightbox-btn-next.gif';
    var nextgen_lightbox_blank_img_url = '/wp-content/plugins/nextgen-gallery/products/photocrati_nextgen/modules/lightbox/static/images/lightbox-btn-prev.gif';

    $('.ngg_lightbox').lightBox({
        imageLoading:  nextgen_lightbox_loading_img_url,
        imageBtnClose: nextgen_lightbox_close_btn_url,
        imageBtnPrev:  nextgen_lightbox_btn_prev_url,
        imageBtnNext:  nextgen_lightbox_btn_next_url,
        imageBlank:    nextgen_lightbox_blank_img_url
    });
});
