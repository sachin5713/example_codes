jQuery(document).ready(function($) {
    var page = 1;
    var loading = false;
    var noMorePosts = false;
    let svgLoader  = `<div class="boat_loader"><svg width="42" height="50" viewBox="0 0 42 50" fill="none" xmlns="http://www.w3.org/2000/svg"><path id="arrow" d="M39.6552 29.3103C39.6552 39.7672 31.1466 48.2759 20.6897 48.2759C10.2328 48.2759 1.72414 39.7672 1.72414 29.3103C1.72414 18.8534 10.1897 10.3793 20.6207 10.3448V18.9655L37 9.48276L20.6207 0V8.62069C9.22414 8.65517 0 17.9052 0 29.3103C0 40.7155 9.25862 50 20.6897 50C32.1207 50 41.3793 40.7328 41.3793 29.3103H39.6552ZM22.3448 2.99138L33.5603 9.48276L22.3448 15.9741V2.99138Z" fill="#99A7DD"/><animateTransform attributeName="transform" attributeType="XML" type="rotate" from="0 0 0"  to="360 0 0"  dur="0.8s" repeatCount="indefinite"/> </svg></div>`;
    // Handle Load More on scroll
    $(window).on('scroll', function() {
        if (!loading && !noMorePosts && $(window).scrollTop() + $(window).height() > $(document).height() - 100) {
            page++;
            loadMorePartners(page);
        }
    });

    $('#partner-search').on('input',function(){
        $('#partner-close-button').show();
    })

    // Handle search button click
    $('#partner-search-button').on('click', function() {
        page = 1;
        noMorePosts = false;
        $('#partner-grid').empty();
        loadMorePartners(page);
    });

    // Handle close button click
    $('#partner-close-button').on('click', function() {
        $('#partner-search').val('');
        $('#partner-close-button').hide();
        page = 1;
        noMorePosts = false;
        $('#partner-grid').empty();
        loadMorePartners(page);
    });

    function loadMorePartners(page) {
        var search = $('#partner-search').val();

        $.ajax({
            url: nbsc.ajax_url,
            type: 'POST',
            data: {
                action: 'load_partners',
                page: page,
                search: search
            },
            beforeSend: function() {
                loading = true;
                $('#load-more').html(svgLoader);
            },
            success: function(data) {
                if (data.trim()) {
                    if (data === false) {
                        noMorePosts = true;
                        $('#load-more').html(nbsc.empty_partner_message);
                    } else {
                        $('#partner-grid').append(data);
                    }
                } else {
                    noMorePosts = true;
                     $('#load-more').html(nbsc.empty_partner_message);
                }
                loading = false;
            },
            error: function() {
                loading = false;
            }
        });
    }

    // Initial load
    // loadMorePartners(page);
});
