(function( $ ) {
	'use strict';

	$(document.body).on('click', '.wtsr_order_item_header', function () {
		var header = $(this);
		var parent = header.parent();
		var bodyContainer = parent.find('.wtsr_order_item_body');

		$('.wtsr_order_item').removeClass('wtsr_order_item_open');
		parent.addClass('wtsr_order_item_open');
	});

	$(document.body).on('change', '[name="wtsr_rating"]', function () {
		var control = $(this);
		var parent = control.parents('.wtsr_order_item_ratings');
		var commentContainer = parent.find('.wtsr_order_item_comment');
		var ratingLabel = parent.find('.rating_label');
		var selectedItem = parent.find('[name="wtsr_rating"]:checked');
		var selectedVal = selectedItem.val();
		var selectedLabel = selectedItem.attr('title');

		ratingLabel.text(selectedLabel);
		commentContainer.show();

		if(navigator.userAgent.match(/(iPod|iPhone|iPad|Android)/)) {
			setTimeout(function() {
				var scrollTop = $(selectedItem).offset().top - 45;
				$(window).scrollTo(scrollTop, 500);
			}, 20);
		}
	});

	$(document.body).on('click', '.wtsr_review_submit, .wtsr_review_submit_dummy', function () {
		var control = $(this);
		var dummy = control.hasClass('wtsr_review_submit_dummy');
		var orderId = control.data('order-id');
		var productId = control.data('product-id');
		var email = control.data('email');
		var reviewId = control.data('review-id');
		var parent = control.parents('.wtsr_order_item_ratings');
		var parentContainer = control.parents('.wtsr_order_item');
		var commentContainer = parent.find('.wtsr_order_item_comment');
		var selectedItem = parent.find('[name="wtsr_rating"]:checked');
		var selectedVal = selectedItem.val();
		var selectedLabel = selectedItem.attr('title');
		var comment = commentContainer.find('.wtsr_comment').val();
		var ajaxurl = wtsrJsObj.ajaxurl;

		var data = {
			action: 'wtsr_ajax_review_submit',
			email: email,
			order_id: orderId,
			product_id: productId,
			comment_post_ID: productId,
			review_id: reviewId,
			rating: selectedVal,
			comment: comment
		};

		if (dummy) {
			data.dummy = 'dummy';
		}

		$.ajax({
			type: 'post',
			url: ajaxurl,
			data: data,

			success: function(response) {
				var decoded;

				try {
					decoded = $.parseJSON(response);
				} catch(err) {
					console.log(err);
					decoded = false;
				}

				if (decoded) {
					if (decoded.success) {
						if (decoded.review_comment) {
							var headerRating = parentContainer.find('.wtsr_order_item_header_rating');
							parent.html(decoded.review_comment);
							headerRating.find('.rating_label').text(selectedLabel);
							headerRating.show();
							parentContainer.addClass('wtsr_reviewed');

							setTimeout(function () {
								var currentIndex = $(parentContainer).index();
								var parentContainers = $('.wtsr_order_item');
								var countParentContainers = parentContainers.length;
								var nextIndex = false;

								if (countParentContainers > 1) {
									parentContainers.each(function(index) {
										if (!$(this).hasClass('wtsr_reviewed') && index > currentIndex) {
											nextIndex = index;
											return false;
										}
									});

									if (!nextIndex) {
										parentContainers.each(function(index) {
											if (!$(this).hasClass('wtsr_reviewed')) {
												nextIndex = index;
												return false;
											}
										});
									}
								}

								if (nextIndex || 0 === nextIndex) {
									var nextParentContainer = $('.wtsr_order_item').eq(nextIndex);
									$( nextParentContainer.find('.wtsr_order_item_header') ).trigger( "click" );

									var scrollTop = nextParentContainer.offset().top - 45;

									$(window).scrollTo(scrollTop, 500);
								}
							}, 1500);
						} else {
							window.location.reload();
						}
					} else {
						alert(decoded.message);
					}
				} else {
					alert('Something went wrong');
				}
			}
		});
	});

	$(document.body).on('keyup', '.wtsr_comment', function () {
		var textarea = $(this);
		var parent = textarea.parents('.wtsr_order_item_comment');
		var min = textarea.data('min-length');
		var btn = parent.find('.comment-form-submit button');

		if (min) {
			var length_holder = parent.find('.wtsr_comment_length');
			var length = textarea.val().length;
			length_holder.text(length);

			if (parseInt(length) < parseInt(min)) {
				btn.addClass('disabled').attr('disabled', true);
			} else {
				btn.removeClass('disabled').attr('disabled', false);
			}
		}
	});

	$(document).ready(function () {
		var review_order_wrapper = $('#wtsr_order_items');

		if (review_order_wrapper.length > 0) {
			var scrollToId = review_order_wrapper.data('scroll-to');
			var scrollTo = $('#wtsr_order_item_' + scrollToId);

			if (scrollTo.length > 0) {
				var scrollTop = $('#wtsr_order_item_' + scrollToId).offset().top - 45;

				if(!navigator.userAgent.match(/(iPod|iPhone|iPad|Android)/)) {
					setTimeout(function() {
						$(window).scrollTo(scrollTop, 500);
					}, 20);
				}

				var rating = scrollTo.find('[name="wtsr_rating"]:checked');
				$( rating ).trigger( "change" );
			} else {
				var scrollTopElement = $('#wtsr_order_items .wtsr_order_item_open');

				if (scrollTopElement.length > 0) {
					var scrollTop = scrollTopElement.offset().top - 45;

					setTimeout(function() {
						$(window).scrollTo(scrollTop, 500);
					}, 1000);
				}
			}
		}
	});

})( jQuery );
