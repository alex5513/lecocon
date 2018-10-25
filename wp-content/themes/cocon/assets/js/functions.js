function search(){
    jQuery('.link-search').click(function(){
        jQuery('.container-search').addClass('is-visible');
        setTimeout(function(){
            jQuery('.aws-search-field').focus();
        }, 100);
    });

    jQuery('.close-popin').click(function(){
        jQuery('.container-search').removeClass('is-visible');
    });
}

function removeMessage(){
    jQuery('.single .woocommerce-error, .single .woocommerce-info, .single .woocommerce-message').addClass('is-visible');

    setTimeout(function(){
        jQuery('.single .woocommerce-error, .single .woocommerce-info, .single .woocommerce-message').removeClass('is-visible');
    }, 3000);
}
