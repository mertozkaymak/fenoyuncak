var btn = '<a style="background: #e00; color: #FFF; margin-top: 10px;" href="javascript:void(0);" class="btn btn-block btn-primary" data-action="createOffer">' +
                        'TEKLİF OLUŞTUR' +
                    '</a>';
					
var info = {};
info["cart"] = {};

var skus = [];

$(document).ready(function() {	

	$(document).on("DOMSubtreeModified", function(){

		let interval = setInterval(function(){

			if(!$("#cart-panel #cart-summary .cart-panel-buttons a[href='/order/step2']").hasClass("d-none disabled")){

				$("#cart-panel #cart-summary .cart-panel-buttons a[href='/order/step2']").addClass("d-none disabled");
				clearInterval(interval);

			}

		}, 1);

	});

	if(localStorage.getItem('skus') != null) {
		skus = JSON.parse(localStorage.getItem('skus'));
	}

	if($(".add-to-cart-button").length) {

		if($(".showcase").length) {
			$(".showcase .add-to-cart-button").attr("onclick", "save(this);");
		}
		
		if($(".showcase-2").length) {
			$(".showcase-2 .add-to-cart-button").attr("onclick", "save(this);");
		}
		
		if($(".product-buttons-row").length) {
			$(".product-buttons-row .add-to-cart-button, .product-buttons-row .quick-order-button").attr("onclick", "save();");
		}

	}
	
	if(window.location.href.indexOf('/sepet') > -1) {
		
		$(".cart-item-quantity").each(function() {
			
			$(this).after($(this).find("input").val() + " adet");
			
		});

		$(document).on("click", "a[data-action=createOffer]", function() {

			let self;
			self = $(this);
			
			if($(".cart-item").length == 0) {
				Swal.fire({title: "Bilgilendirme", text: "Sepetinizde ürün bulunmamaktadır.", icon: "info", confirmButtonText: "Tamam"});
				return false;
			}

			let formHTML = `<div class="form-group">
								<label>Firma Adı *</label><input type="text" placeholder="Firma Adı" class="large _typeInput" id="firm">
							</div>
							<div class="form-group">
								<label>Adınız / Soyadınız *</label><input type="text" placeholder="Adınız/Soyadınız" value="${(typeof visitorInfo !== "undefined" && (visitorInfo["firstname"] !== "" && visitorInfo["surname"] !== "")) ? visitorInfo["firstname"] + " " + visitorInfo["surname"] : ""}" class="large _typeInput" id="name">
							</div>
							<div class="form-group">
								<label>E-Mail adresiniz *</label><input placeholder="E-Mail adresiniz" type="email" value="${(typeof visitorInfo !== "undefined" && visitorInfo["email"] !== "") ? visitorInfo["email"] : ""}" class="large _typeInput" id="email">
							</div>
							<div class="form-group">
								<label>Telefon *</label><input placeholder="Telefon" type="tel" class="large _typeInput" id="phone">
							</div>
							<div class="form-group">
								<label>Not</label><textarea placeholder="Firmaya iletmek istediğiniz durumu buraya girebilirsiniz." class="form-group" id="note"></textarea>
								<label>(0/255)</label>
							</div>`;
			
			Swal.fire({
				title: 'SİPARİŞİ OLUŞTUR',
				html: formHTML,
				allowOutsideClick: false,
				allowEscapeKey: false,
				showCancelButton: true,
				showDenyButton: false,
				confirmButtonText: 'GÖNDER',
				cancelButtonText: 'Vazgeç',
				reverseButtons: true,
				willOpen: () => {
					$(".swal2-popup .swal2-actions").addClass("form-offer");
					$(".swal2-popup .swal2-actions .swal2-confirm").addClass("swal2-form-confirm");
					$(".swal2-popup .swal2-actions .swal2-cancel").addClass("swal2-form-cancel");
				}
			}).then((result) => {

				if (result.isConfirmed) {
					
					info["email"] = (typeof visitorInfo !== "undefined" && visitorInfo["email"] !== "") ? visitorInfo["email"] : $("#email").val();
					info["name"] = (typeof visitorInfo !== "undefined" && (visitorInfo["firstname"] !== "" && visitorInfo["surname"] !== "")) ? visitorInfo["firstname"] + " " + visitorInfo["surname"] : $("#name").val();
					info["firm"] = $("#firm").val();
					info["phone"] = $("#phone").val();
					info["note"] = $("#note").val();

					if(!emailIsValid(info["email"])) {
						Swal.fire({title: "Bilgilendirme", text: "Lütfen geçerli bir e-mail adresi giriniz.", icon: "info", confirmButtonText: "Tamam", willClose: function(){
							self.trigger("click");
						}});
						$(".swal2-popup #email").css({"border-color": "#CC0000"});
						return false;
					}
					
					if(info["name"].trim().length == 0) {
						Swal.fire({title: "Bilgilendirme", text: "Lütfen adınızı / soyadınızı giriniz.", icon: "info", confirmButtonText: "Tamam", willClose: function(){
							self.trigger("click");
						}});
						$(".swal2-popup #name").css({"border-color": "#CC0000"});
						return false;
					}

					if(info["phone"].trim().length == 0) {
						Swal.fire({title: "Bilgilendirme", text: "Lütfen telefon numaranızı giriniz.", icon: "info", confirmButtonText: "Tamam", willClose: function(){
							self.trigger("click");
							$(".swal2-popup #phone").css({"border-color": "#CC0000"});
						}});
						return false;
					}

					if(info["firm"].trim().length == 0) {
						Swal.fire({title: "Bilgilendirme", text: "Lütfen firma adınızı giriniz.", icon: "info", confirmButtonText: "Tamam", willClose: function(){
							self.trigger("click");
							$(".swal2-popup #firm").css({"border-color": "#CC0000"});
						}});
						return false;
					}

					if(info["note"].trim().length == 0) {
						info["note"] = "İletilmesi istenen bir not bulunmamaktadır.";
					}
					
					info["cart"] = {};
					
					$(".cart-item").each(function() {
						
						var product = {};
						product["name"] = $(this).find(".cart-item-name a:eq(0)").text().trim();
						product["quantity"] = $(this).find(".cart-item-quantity input").val();
						product["price"] = $(this).find(".item-rebate-price").text().replace(".","").replace(",",".").replace(" TL", "").trim();
						// product["tax"] = $(this).find(".item-tax").text().replace("+ KDV % ","").trim();
						product["sku"] = "";

						for(i = 0; i < skus.length; i++) {

							if(product["name"] == skus[i][0]) {
								product["sku"] = skus[i][1];
							}

						}

						info["cart"][Object.keys(info["cart"]).length] = product;
						
					});

					Swal.fire({
						title: 'Teklifiniz oluşturuluyor...',
						html: '<img src="***/images/loading.gif" width="120"/>',
						allowOutsideClick: false,
						allowEscapeKey: false,
						allowEnterKey: false,
						willOpen: () => {
							Swal.showLoading();
						},
						showConfirmButton: false,
						showCancelButton: false,
						showDenyButton: false
					});

					$.ajax({
						type: "GET",
						url: "***/pdfgenerator",
						data: "info=" + JSON.stringify(info),
						success: function(response){

							console.log(response);

							if(response == 1) {
							
								Swal.fire({
									icon: "success",
									title: "Tebrikler!",
									text: "Teklifiniz oluşturuldu. Lütfen e-posta kutunuzu kontrol ediniz.",
									confirmButtonText: "Tamam"
								});
									
							}
							else if(response == "errMail"){

								Swal.fire({
									icon: "warning",
									title: "Hata Oluştu",
									text: "Teklifiniz oluşturuldu. Fakat teknik bir sorundan dolayı mail atılamamıştır.",
									confirmButtonText: "Tamam"
								});

							}
							else {

								Swal.fire({
									icon: "error",
									title: "Hata Oluştu",
									text: "Lütfen tekrar deneyiniz.",
									confirmButtonText: "Tamam"
								});
							
							}
							
						}
					});
					
				}

				$(".swal2-popup .swal2-actions").removeClass("form-offer");
				$(".swal2-popup .swal2-actions .swal2-confirm").removeClass("swal2-form-confirm");
				$(".swal2-popup .swal2-actions .swal2-cancel").removeClass("swal2-form-cancel");
				
			});
			
		});

		$(document).on("focus", ".swal2-popup input", function(){

			if($(this).attr("id") == "firm" || $(this).attr("id") == "email" || $(this).attr("id") == "name" || $(this).attr("id") == "phone"){

				if(typeof $(this).attr("style") !== "undefined"){

					$(this).removeAttr("style");

				}

			}

		});

		$(document).on("input", ".swal2-popup .swal2-content textarea", function(){

			let thisval = $(this).val();
			if(thisval.length > 255){

				$(this).on("keypress", function(e){

					e.preventDefault();
					e.stopImmediatePropagation();

				});

				return false;

			}

			$(this).unbind("keypress");
			$(this).parent().find("label:eq(1)").text("(" + thisval.length + "/255)");

		});

		$(document).on("keydown", ".swal2-popup .swal2-content textarea", function(e){

			let thisval = $(this).val();
			if(thisval.length > 255){

				e.preventDefault();
				e.stopImmediatePropagation();

			}

		});
		
		$(document).on("keyup", ".swal2-popup .swal2-content textarea", function(){

			let thisval = $(this).val();
			if(thisval.length > 255){

				let temp = thisval.length - 255;
				$(this).val(thisval.substring(0, thisval.length - temp));
				$(this).trigger("input");

			}

		});
		
		if($("a[data-action=createOffer]").length == 0) {
			
			if($(".cart-panel-buttons").find("a[href='/order/step2']").attr("class").indexOf("d-none") == -1){

				// $(".cart-panel-buttons").find("a[href='/order/step2']").addClass("d-none");

			}

			$(".cart-panel-buttons").append(btn);
			
		}
		
	}
			
});

function calculate(elem) {
	
	var pquantity = elem.parents(".showcase").find("#pquantity").attr("data-value");
	var quantity = elem.parent().find("input").val();

	var qq = pquantity * quantity;
	elem.parents(".showcase").find(".add-to-cart-button").attr("data-quantity", qq);
	elem.parents(".showcase").find("#pquantity").text(qq);
	
}

function calculate2() {
	
	var pquantity = $(".pquantity").attr("data-value");
	var quantity = $("#qty-input").val().replace(" Koli", "").replace(" Adet", "");
	
	console.log(pquantity);
	
	if(pquantity == 1) {
		$("#qty-input").val($("#qty-input").val().replace(" Koli", "").replace(" Adet", "") + " Adet");
	}
	else {
		$("#qty-input").val($("#qty-input").val().replace(" Koli", "").replace(" Adet", "") + " Koli");
	}

	var qq = pquantity * quantity;
	$(".product-buttons-row .quick-order-button, .product-buttons-row .add-to-cart-button").attr("data-quantity", qq);
	
}

function emailIsValid (email) {
	return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
  }

function save(elem = "") {
	
	if(elem != "") {
		elem = $(elem);
	}

	var name = elem != "" ? elem.parents(".showcase,.showcase-2").find(".showcase-title").text().trim() : $("h1:eq(0)").text().trim();
	var sku = elem != "" ? elem.parents(".showcase,.showcase-2").find(".showcase-stock-code").text().trim() : $(".product-list-title:contains('Stok Kodu')").next().text().trim();

	var defined = 0;

	for(i = 0; i < skus.length; i++) {

		if(skus[i][1] == sku) {
			defined = 1;
		}

	}

	if(defined == 0) {
		skus.push([name, sku]);
		localStorage.setItem('skus', JSON.stringify(skus));
	}

}

function number_format(number, decimals, dec_point, thousands_sep) {
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}
