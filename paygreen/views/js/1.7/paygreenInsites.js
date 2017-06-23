/**
* 2014 - 2015 Watt Is It
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PayGreen <contact@paygreen.fr>
*  @copyright 2014-2014 Watt It Is 
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
*/

(function($) {
  $.fn.paygreenInsites = function(options)
  {
  	var defautlts = 
  	{
  		"id" : "1",
  		"amount" : "",
  		"asso": {"assosciationId": "", "name": "", "imageUrl": ""},
  		"module": "none"
  	};
  	var params=$.extend(defautlts, options);
  	var maxDonation = Math.ceil(params.amount * 0.2);
    return this.each(function() {
    	var doc = $(this);
    	var select,
    	donation = false;

    	function init() {
			loadScript();
			doc.html(createHtml());
			$("#insiteTpl").tmpl(params).appendTo("#insites" + params.id);
		    imageControl();
		    numberInput();
		    var popID = selectorById('holds-the-iframe').parent().attr('id').slice(0,16)  +"-additional-information";
			var reg = new RegExp('.*insite=' + params.id + '.*', 'i');
			if (!reg.test($(location).attr('href'))) {
			} else {
				$('#insites' + params.id).removeClass('hidden');
				selectorById('holds-the-iframe').removeClass('hidden');
				selectorById('holds-the-iframe').parent().addClass("popup_block");
				control();
				document.getElementById(selectorById('holds-the-iframe').parent().attr('id').slice(0,16)).click();
			}
			checked();
			//input For payment option
		};

		//FRONT SECTION
		function createHtml() {
    		var html = 
    		'<script id=\"insiteTpl\" type=\"text/x-jQuery-tmpl\">' + 
		    	'<div id=\"message${id}\">' + 
		    	'</div>' +
				'<div class=\"checkbox\">' +                            
					'<input type=\"checkbox\" class=\"option ${id} \" value=\"\"><label for= \"option ${id}\""> Option: arrondir votre panier et faire un don</label>' +
				'</div>' +

				'<p class=\"choose ${id}\">Choisissez votre association:</p>' +
				'<div class=\"overlay ${id}\">' +
					'{{each asso}}' +
	            		'<a class=\"assoc ${id} \" data-id=\"${$value.associationId}\">' +
							'<div class=\"imgAssoc\" style="background-image:url(${$value.imageUrl})\"></div>' +
							'<br>' +
							'<span>${$value.name}</span>' +
						'</a>' +
					'{{/each}}' +
				'</div>' +
				'<div class="col-xs-11 col-md-10 price ${id}">' +
					'<div class="row">' +
  						'<div class="col-xs-4 col-md-4">Panier</div>' +
  						'<div class="col-xs-4 col-md-4">Don</div>' +
  						'<div class="col-xs-4 col-md-4">Total</div>' +
					'</div>' +
					'<div class="row">' +
  						'<div class="col-xs-4 col-md-4" id="panier${id}"></div>' +
  						'<div class="col-xs-4 col-md-4" id="don${id}"></div>' +
  						'<div class="col-xs-4 col-md-4" id="total${id}"></div>' +
					'</div>' +
				'</div>' +
				'<p class=\"choice ${id}\">Je choisis de donner:</p>' +
				'<div class=\"container\">' +
		 			'<form action="" method="post" class="donation-tools ${id}">' +
		    			'<div class=\"range-slider ${id} col-xs-12 col-sm-6 ' + '\">' +
		    				'<input type=\"text\" class=\"range form-control ${id}\" value=\"\" />' +
						'</div>' +
						'<div class="form-group">' +
							'<div class=\"col-xs-7 col-sm-4\">' +
		   						'<input type=\"number\" class=\"number ${id} form-control input-md\" step =\"0.01\" value=\"0.01\" min = \"0.01\"/>' +
		   					'</div>' +
		   					'<div class=\"number euro ${id} col-xs-3 col-md-1\">&euro;</div>' +
		   				'</div>' +
					'</form>' +
				'</div>' +
				'<pre>' +
				'</pre>' +
				'<div class=\"panel-body\">' +
					'<div class=\"info ${id} collapse\">'  +
		    			'<p class=\"light ${id}\">Vous décidez du montant de votre arrondi en faisant glisser le curseur ci-dessus. Cliquez ensuite sur le bouton j\'arrondi pour arrondir le montant de votre panier.</p>' +
		    			'<p>Si vous ne shouhaitez pas faire d\'arrondi, décochez la case option.</p>' +
		    		'</div>' +
				'</div>' +
				'<div class=\"form-group choice ${id}\"> ' +
					'<div class=\"col-xs-2 col-md-2">' +
					'</div>' +
					'<div class=\"col-xs-8 col-md-8 buttonDonation ${id}\">' +
						'<button  type=\"button\" class=\"cancel ${id} btn btn-danger\" >Annuler</button>' +
						'<button  type=\"submit\" class=\"roundOff ${id} btn btn-success\" name="submitPaygreenInsites" >J\'arrondis</button>' +
					'</div>' +
				'</div>' + 
			'</script>';
	    	return html;
    	}

    	function loadScript() {
    		$.holdReady(true);
			function releaseHold() { $.holdReady(false); }

			$.getScript("modules/paygreen/views/js/1.7/ion.rangeSlider.js", releaseHold);
    		
			$.getScript("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js", releaseHold);

			$.getScript("modules/paygreen/views/js/1.7/jquery.tmpl.min.js", releaseHold);
    	}

    	function numberInput() {
		    var $range = $(".range." + params.id + ".form-control"),
	    	$input = $(".number." + params.id + ".form-control"),
	    	instance,
	    	min = 0.01,
	    	max = maxDonation;
	    	round = (Math.ceil(params.amount) - params.amount).toFixed(2);
	    	euroCeil = round > 0.01 ? round : 1;
			$range.ionRangeSlider({
	    		type: "single",
	    		min: min,
	    		max: max,
	    		from: euroCeil,
	    		step: 0.1,
	    		hide_min_max: true,
	   			hide_from_to: true,
	   			keyboard: true,
	   			keyboard_step: 0.1,
	    		onStart: function (data) {
	       			$input.prop("value", data.from.toFixed(2));
	    		},
	    		onChange: function (data) {
	        		$input.prop("value", data.from.toFixed(2));
	    		}
			});

			instance = $range.data("ionRangeSlider");

			$input.on("input", function () {
	    		var val = $(this).prop("value");
	    
	    		// validate
	    		if (val < min) {
	        		val = min;
	    		} else if (val > max) {
	        		val = max;
	    		}
	    
	    		instance.update({
	        		from: val
	    		});
	    		$(".range-slider." + params.id + " > span").show();
			});
		};

		//LISTENERS SECTION
		function checked() {
			if(selectorById("option").is(':checked')){
				if (donation == true) {
					acceptedDonation();
				} else {
					startDonation();
				}
			} else {
				if (donation == true) {
					cancelDonation();
				}
				startDisplay();
			}
		};

		function control() {
			roundOffControl();

			if (isPresta()) {
				prestaIntegration();
			};

			$(".cancel." + params.id).on('click', function() {
				selectorById("option").prop('checked', false);
				cancelDonation();
				checked();
			});

            $('.option.' + params.id + ' + label').on('click', function () {
            	selectorById('option').prop('checked', !selectorById("option").is(':checked'));
            	checked();
            });
		};

		function imageControl() {
			selectorById("assoc").on('click', function() {
				if (donation == false) {
					selectorById("assoc").removeClass("selected");
					 $(this).addClass("selected");
					selectorById("option").prop("checked", true);
					select = $(this).data('id');
					checked();
				}
			});
		};

		//MODULE SECTION
		function isPresta() {
			return params.module == 'prestashop';
		}

		function prestaIntegration() {
			$("#" + selectorById('holds-the-iframe').parent().attr('id').slice(0,16)).on('click',function () {
				$(".option." + params.id).prop('checked', false);
				checked();
				var number = selectorById('number');
				if (number.val() != euroCeil) {
					number.val(euroCeil);
					number.change();

				}
				if ($('#iframeInsites' + params.id).attr('src') == undefined || $('#iframeInsites' + params.id).attr('src') == "") {
					$('#iframeInsites' + params.id).attr('src', $('#iframeInsites' + params.id).data('src'));
					$(".iframeInsites " + params.id + "> iframe").load(function() {
						selectorById('holds-the-iframe').addClass("loaderPaygreen").fadeOut("slow");
					});
				}
				popIN();
            });

            document.getElementById(selectorById('holds-the-iframe').parent().attr('id').slice(0,16)).click();
		}
		//TOOLS
		function popIN() {
			var popID = selectorById('holds-the-iframe').parent().attr('id').slice(0,16)  +"-additional-information";
			console.log($('#' + popID).width());
			var popWidth = $(window).width() <= 500 ? $(window).width() : 500;
			var popHeight = $(window).height() <= 550 ? $(window).height() : 550;
			console.log('le longueur est ' + popWidth);
			$('#' + popID).fadeIn().css({ 'width': popWidth, 'height': popHeight}).prepend('<a href="#" class="close"></a>');
			console.log($('#' + popID).width());
			var popMargTop = ($(window).height()) / 2;
			var popMargLeft = ($('#' + popID).width() + 80) / 2;						
			$('#' + popID).css({ 
				'margin-top' : -(popMargTop - 20),
				'margin-left' : -popMargLeft
			});
					
			$('body').append('<div id="fade"></div>');
			$('#fade').css({'filter' : 'alpha(opacity=80)'}).fadeIn();
			$('body').on('click', '#fade', function() {
				$('#fade , .popup_block').fadeOut(function() {
					$('#fade').remove(); 
				});
			});
		}

		function roundOffControl() {
			$(".roundOff." + params.id).on('click', function() {
				amount = $(".number.form-control." + params.id).val();
				if (!selectorById("option").is(':checked')) {
					displayError('Veuillez cocher l\'option et selectionner une association');
				} else if (select == null) {
					displayError('Entrez une association');
				} else if (amount <= 0) {
					displayError('Entrez une somme supérieur ou égale à 0.01€');
				} else {
					$.post(
    					'modules/paygreen/rounding.php',
    					{
        					associationId : select,
        					amount : $(".number.form-control." + params.id).val(),
        					paiementToken : document.getElementById("iframeInsites" + params.id).src.split("/")[5].split("?")[0].substring(2),
    					}
    				)
    				.done(function(data) {
    					data = JSON.parse(data);
    					console.log(data);
    					console.log(document.getElementById("iframeInsites" + params.id).src.split("/")[5].split("?")[0].substring(2));
						if(data.success == false) {
							displayError('une Erreur est survenue.Veuillez réessayer.');
						} else {
							document.getElementById("iframeInsites" + params.id).src = document.getElementById("iframeInsites" + params.id).src;
							$(".iframeInsites " + params.id + "> iframe").load(function() {
								selectorById('holds-the-iframe').addClass("loaderPaygreen").fadeOut("slow");
							});
							updateBill(data.data.amount / 100);
							donation = true;
							checked();
						}
					})

    				.fail(function(data) {
    					displayError('une Erreur est survenue.Veuillez réessayer.');
    				});
				}
			});
		}
		function deleteDonation() {
			$.post(
    			'modules/paygreen/rounding.php',
    			{
        			cancelRounding : true,
        			paiementToken : $('#iframeInsites' + params.id).data('src').split("/")[5].split("?")[0].substring(2),
    			}
    		)
    		.done(function(data) {
    			data = JSON.parse(data);
				if(data.success == true) {
					document.getElementById("iframeInsites" + params.id).src = document.getElementById("iframeInsites" + params.id).src;
					$(".iframeInsites " + params.id + "> iframe").load(function() {
						selectorById('holds-the-iframe').addClass("loaderPaygreen").fadeOut("slow");
					});
					donation = false;
				} else {
					displayError("Une erreur est survenue.Veuillez réessayer.");
				}
			})
			.fail(function(data) {
    			displayError('une Erreur est survenue.Veuillez réessayer.');
    		});
		}

		function cancelDonation() {
			$.post(
    			'modules/paygreen/rounding.php',
    			{
        			getRounding : true,
        			paiementToken : $('#iframeInsites' + params.id).data('src').split("/")[5].split("?")[0].substring(2),
    			}
    		)
    		.done(function(data) {
    			if (data != "null") {
    				deleteDonation();
    			}
    		})

    		.fail(function(data) {
    		});
		}

		function displayError(message) {
			$('#message' + params.id).html('<div class="alert alert-danger fade in">' 
				+ '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' 
				+ message 
				+ '</div>'
			);
		};

		function displaySuccess(message) {
			$('#message' + params.id).html('<div class="alert alert-success fade in ">'
				+ "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>"
				+ message
				+ "</div>"
			);
		}


		function selectorById(selector) {
    		return $("." + selector + "." + params.id);
    	}

		function nameAssociation(id) {
			for (var assosication in params.asso) {
				if (assosication.associationId == id) {
					return assosication.name;
				}
			}

			return "";
		}

		function updateBill(amount) {
			$("#panier" + params.id).html(params.amount + " €");
			$("#don" + params.id).html(amount.toFixed(2) + " €");
			$("#total" + params.id).html((params.amount + amount).toFixed(2) + " €");
		}

		function startDisplay() {
			select = null;
			selectorById("price").slideUp();
			selectorById("donation-tools").slideUp();
			selectorById('choice').slideUp();
			selectorById('info').slideUp();
			$("#iframeInsites" + params.id).slideDown();
			selectorById("assoc").removeClass("assocDonation").removeClass("selected");
			selectorById('holds-the-iframe').removeClass("acceptedDonation");
			selectorById("overlay").removeClass('overlayDonation');
			selectorById("choose").slideDown();
		}

		function startDonation() {
			selectorById("price").slideUp();
			$("#iframeInsites" + params.id).slideUp();
			selectorById("donation-tools").slideDown();
			selectorById('choice').slideDown();
			selectorById('info').slideDown();
			if (selectorById("assoc").length > 1) {
				selectorById("assoc").addClass("assocDonation");
			} else {
				selectorById("assoc").addClass("selected");
				select = selectorById("assoc").data('id');
			}
		}

		function acceptedDonation() {
			selectorById("price").slideDown();
			selectorById("donation-tools").slideUp();
			selectorById('choice').slideUp();
			selectorById('info').slideUp();
			$("#iframeInsites" + params.id).slideDown();
			selectorById("assoc").removeClass("assocDonation");
			$(".assoc." + params.id + "[data-id=" + select + "]").addClass("selected");
			selectorById('holds-the-iframe').addClass("acceptedDonation");
			selectorById("choose").slideUp();
			selectorById("overlay").addClass('overlayDonation');
		}

		init();

    });
  };
})(jQuery);
