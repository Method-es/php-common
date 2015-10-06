// Common.js



var Strut = Strut || {};


Strut.Common = {
	BaseURL: null,
	LoadedJS: [],
	ANIMATION_TIME: 400,
    ENVIRONMENT: 'development',

	Initialize: function(BaseURL, ENVIRONMENT){
		Strut.Common.BaseURL = BaseURL;	
        Strut.Common.ENVIRONMENT = ENVIRONMENT;

		$('body').on('click','.dialogue-mask', function(){
			Strut.Common.CloseDialogs();
		});

		$('body').on('click.ajax-links', '[data-ajax-link]', function(event){
			event.preventDefault();
			var that = $(this);
			$.ajax({url:that.attr('href'),success:function(data){
				if(that.attr('data-ajax-refresh') === ""){
					document.location.reload();
				}
			}});
		});

		Strut.Common.Template.Init();
		Strut.Common.Form.Init();

	},

	ClearPlaceholder: function(input, isContentEditable){
		if(isContentEditable){
			if(input.text() == input.data('placeholder'))
				input.text('');
		} else {
			if(input.val() == input.attr('placeholder'))
				input.val('');
		}
	},

	ReplacePlaceholder: function(input, isContentEditable){
		if(isContentEditable){
			if(input.text() == '')
				input.text(input.data('placeholder'));
		} else {
			if(input.val() == '')
				input.val(input.attr('placeholder'));
		}
	},


	Redirect: function(location){
		window.location = Strut.Common.BaseURL + location;
	},

	ValidateResult: function(data, callback, noError, noRedirect){

		if(typeof data == "object"){
			if("Error" in data && !noError){
				callback(data);
				return false;
			}
			if("Location" in data && !noRedirect){
				Strut.Common.Redirect(data['Location']);
			}
		}

		return true;
	},

	AJAX: {

        ValidateResult: function(data, options){

            // COPY: Strut.Common.AJAX.ValidateResult(data,{});

            // this function provides some basic boiler plate code to handle 
            // most conventions we use for returning json packages
            // if an error is detected you can provide a callback
            // if a location is seen it will redirect them
            // you can disable both of the above features with flags
            // if you disable both features then the function will only 
            // check to see if it is an object or not 
            var opts = $.extend(true,{},{
                errorCallback: undefined,
                exceptionCallback: undefined,
                ignoreError: false,
                ignoreException: false,
                ignoreRedirect: false,
                errorDialog: false,
                exceptionDialog: true // only exceptions are shown by default
            }, options);

            if(typeof data == "object" && data){
                if("Location" in data && !opts.ignoreRedirect){
                    Strut.Common.Redirect(data['Location']);
                }
                if("Error" in data && !opts.ignoreError){
                    if(typeof opts.errorCallback == 'function')
                        opts.errorCallback(data);
                    if(opts.errorDialog){
                    	var dialog = Strut.Common.Dialog.Create({
					        content: data.Error, 
					        title: "An Error Occured", 
					        size: "sm"
					    });
                    }
                    return false;
                }
                if("Exception" in data && !opts.ignoreException){
                    if(typeof opts.exceptionCallback == 'function')
                        opts.exceptionCallback(data);
                    if(opts.exceptionDialog){
                    	var dialog = Strut.Common.Dialog.Create({
					        content: Strut.Common._GetExceptionDisplay(data.Exception), 
					        title: "Something Exceptional Occurred", 
					        size: "sm"
					    });
                    }
                    return false;
                }
            }else{
                return false;
            }
            return true;
        },
    },

    _GetExceptionDisplay: function(exception){
    	if(typeof exception == 'string'){
    		return exception;
    	}else if(exception && typeof exception == 'object'){
    		var hideDetails = " style=\"display:none\"";
    		hideDetails = "";
    		var output = $("<ul>");
    		if('Code' in exception){
    			output.append("<p><strong>Code:</strong> "+exception.Code+"</p>");
    		}
    		if('Message' in exception){
    			output.append("<p><strong>Message:</strong> "+exception.Message+"</p>");
    		}
    		if('File' in exception){
    			output.append("<p"+hideDetails+"><strong>File:</strong> "+exception.File+"</p>");
    		}
    		if('Line' in exception){
    			output.append("<p"+hideDetails+"><strong>Line:</strong> "+exception.Line+"</p>");
    		}
    		if('Trace' in exception){
    			var trace = exception.Trace;
    			console.log(trace);
    			if(typeof trace == "string")
    				trace = trace.replace(/\n|\r/gi,"<br />");
    			else
    				trace = trace.join('\n');
    			output.append("<p"+hideDetails+"><strong>Trace:</strong><br/>"+trace+"</p>");
    		}
    		
    		return output;
    	}
    },

	Dialog: {
		Create: function(options){
				
			//remove existing dialogs... ? lil brute force but ... =/
			// var existingDialog = $('body').find('[data-dialog]');
			// if(existingDialog.length > 0){
			// 	existingDialog.fadeOut(Strut.Common.ANIMATION_TIME, function(){
			// 		$(this).remove();
			// 	})
			// }

			var opts = {
				buttons: [ {
					"text": 'Okay',
					"callback": undefined, 
					"class":"btn-default", 
					"autoClose": true,
					"name": undefined 
				} ],
				title: "Placeholder Title",
				content: "Placeholder Content",
				size: "",
				closeCallback: undefined,
				extras: [],
				// input: {type:'text', acceptTypes: ""}
			};
			$.extend(opts,options);
					
			var dialog = Strut.Common.Template.GetTemplateClone('dialog-template');
			dialog.data('dialog-data', opts);

			//reset the dialog
			// dialog.find('#file-input').hide();
			// dialog.find('#text-input').hide();
			// dialog.find('.btns').empty();

			//var leadEle = dialog.find('.lead').text('');
			//var contentEle = dialog.find('.content').text('');

			dialog.find('[data-dialog-title]').html(opts.title);
			dialog.find('[data-dialog-content]').html(opts.content);

			$.each(opts.extras,function(key,value){
				dialog.find('[data-dialog-extras]').append(value);
			});

			//var btnSize = 12/opts.buttons.length;
			$.each(opts.buttons, function(key, value){
				//var newBtn = dialog.find('#btn-template').children().clone();
				var newBtn = Strut.Common.Template.GetTemplateClone('dialog-btn-template');
				var lbl = newBtn.find('[data-dialog-button-text]');
				if(lbl.length == 0)
					lbl = newBtn;
				lbl.html(value.text);
				if(value['class'] == undefined)
					value['class'] = "btn-default";
				var autoClose = true;
				if(value['autoClose'] != undefined)
					autoClose = value['autoClose'];
				if(value['name'])
					newBtn.attr('data-dialog-button-name',value['name']);
				else
					newBtn.attr('data-dialog-button-name',value['text'].toLowerCase());
				newBtn.addClass(value['class']);
				// var wrap = $('<div>');
				// wrap.addClass('col-xs-'+btnSize);
				// wrap.append(newBtn);
				//dialog.find('[data-dialog-buttons]').append(wrap);
				dialog.find('[data-dialog-buttons]').append(newBtn);
				newBtn.unbind('click').click(function(event){
					if(autoClose)
						Strut.Common.Dialog.Close(dialog);
					if(typeof value.callback == 'function')
						value.callback();
				});
			});
			
			if(opts.size != ""){
				dialog.find('.dialog').addClass('dialog-'+opts.size);
				if(opts.size == "md"){
					$.each(dialog.find('*'), function(){
						$(this).removeClass(function(index, css){
						    return (css.match (/(^|\s)col-lg-\S+/g) || []).join(' ');
						});
					});
				}
				if(opts.size == "sm"){
					$.each(dialog.find('*'), function(){
						$(this).removeClass(function(index, css){
						    return (css.match (/(^|\s)col-lg-\S+|col-md-\S+/g) || []).join(' ');
						});
					});
				}
			}

			// if('input' in opts){
			// 	dialog.find('#'+opts.input.type+'-input').show();
			// 	if('acceptTypes' in opts.input){
			// 		dialog.find('#'+opts.input.type+'-input input').attr('accept',opts.input.acceptTypes);
			// 	}else{
			// 		dialog.find('#'+opts.input.type+'-input input').attr('accept','');
			// 	}
			// }

			//dialog setup is done, animate
			var templateLocation = "body";
			if($('[data-dialog-location').length > 0)
				templateLocation = '[data-dialog-location]';

			dialog.prependTo(templateLocation).css('opacity', 0);
			setTimeout(function(){
				dialog.find('.dialog').addClass('open');
			}, 10);
			TweenLite.to(dialog, 0.2, {opacity: 1, ease: 'Power1.easeInOut'});
			
			$('[data-dialog-close]').unbind('click').click(function(){
				Strut.Common.Dialog.Close(dialog);
			});
			
			return dialog;
		},

		Close: function(dialog){
			var opts = dialog.data('dialog-data');
			if(typeof opts.closeCallback == 'function'){
				opts.closeCallback();
			}
			dialog.find('.dialog').removeClass('open');
			setTimeout(function(){
				TweenLite.to(dialog, 0.2, {opacity: 0, ease: 'Power1.easeInOut', onComplete: function(){
					dialog.remove();
				}});
			}, 50);
		},

		Confirmation: function(title, callback){
			Strut.Common.Dialog.Create({
				title: 'Confirm',
				content: "",
				extras: [title],
				buttons: [
					{
						text: '<span class="fa fa-times"></span> Cancel',
						callback: function(){}, 
						class:"btn-danger" 
					},
					{
						text: '<span class="fa fa-check"></span> OK',
						callback: function(){
							callback();
						},
						class:"btn-primary"
					}
				]
			});
		},
	},

	LoginAction: function(options){
		var opts = {
			email: "",
			password: "",
			origin: "",
			referer: "",
			callback: undefined
		};
		$.extend(opts,options);
		$.ajax({
			url: Strut.Common.BaseURL + 'Auth/Login',
			type: 'POST',
			dataType: 'json',
			data: { email: opts.email, password: window.btoa(opts.password), origin: opts.origin, referer: opts.referer },
			success: function(data){
				if(Strut.Common.AJAX.ValidateResult(data, { ignoreError: true, ignoreRedirect: true })){
					if(typeof opts.callback != 'undefined')
						opts.callback(data);
					data = null; //apparently an IE memory leak fix	
				}
			}
		});

	},

	ShowLoader: function(){
		$('body').append('<div class="page-mask loader-mask"></div>');
		$('.loader-mask').fadeIn();
		$('.loader-mask').css({'opacity':1, 'display':'block'});

		var loader = $('<div class="loader-background"><img src="'+Strut.Common.BaseURL+'insynergi_engine/assets/img/ins-loading-100.gif" alt="Loading..." /></div>')

		$('.loader-mask').append(loader);
	},

	HideLoader: function(){
		$('.loader-mask').fadeOut();
		$('.loader-mask').css({'opacity':0, 'display':'none'});
		$('.loader-mask').remove();
	},

	LazyLoadCSS: function(cssFiles){
		for(var i=0;i<cssFiles.length;i++){
			$('<link>', {rel:'stylesheet', type:'text/css', 'href':cssFiles[i]} ).on('load', function(){
				
			}).appendTo('head');
		}
	},

	LazyLoadJS: function(jsFiles, callback){
		Strut.Common.QueueSize = jsFiles.length;
		Strut.Common.QueueCallback = callback;
		for(var i=0;i<jsFiles.length;i++){
			if($.inArray(jsFiles[i],Strut.Common.LoadedJS) == -1){
				Strut.Common.LoadedJS.push(jsFiles[i]);
				$.ajax({
					url: jsFiles[i],
					dataType: "script",
					success: Strut.Common._FinishedLoadingCustomJS,
					async: false //things load too quickly when done async, so we need to wait
				});
			} else {
				Strut.Common._FinishedLoadingCustomJS();
			}
		}
	},

	_FinishedLoadingCustomJS: function(){
		Strut.Common.QueueSize--;
		if(Strut.Common.QueueSize === 0 && typeof Strut.Common.QueueCallback === 'function')
			Strut.Common.QueueCallback();
	},

	ResetSaveButton: function(saveButton, buttonText){
		if(buttonText == null)
			buttonText = 'Save'; 
		if(saveButton.hasClass('btn-success'))
			saveButton.removeClass('btn-success').addClass('btn-primary');
			saveButton.html(buttonText);
	},

	SaveToSuccess: function(saveButton, buttonText){
		if(buttonText == null)
			buttonText = 'Saved'; 
		saveButton.removeClass('btn-primary').addClass('btn-success');
		saveButton.html(buttonText+"&nbsp;<span class=\"glyphicon glyphicon-ok\"></span>");
	},

	ExecuteFunction: function(functionName, context){
		var args = [].slice.call(arguments).splice(2);
		var namespaces = functionName.split(".");
		var func = namespaces.pop();
		for(var i = 0; i < namespaces.length; i++) {
			context = context[namespaces[i]];
		}
		return context[func].apply(this, args);
	},

	Paginator: {
		Paginations: {},
		Parse: function(selector){
			var ele = $(selector);
			Strut.Common.Paginator.Init(selector,ele.data('dir'),ele.data('column'),ele.data('source'));
		},
		Init: function(listID, direction, columnMajor, source, callback){
			var opts = {
				direction: direction,
				column: columnMajor,
				offset: 0,
				limit: $(listID+' .results_per_page').val(),
				currentPage: 0,
				totalPages: 0,
				callback: callback,
				dataSource: source,
				extras: $(listID).data('extras')
			};
			Strut.Common.Paginator.Paginations[listID] = opts;
			$(listID+' .spinner').hide();
			$(listID+' .results_per_page').siblings('.custom-dropdown').find('p').html($(listID+' .custom-dropdown-holder select option:selected').text());
			$(listID+' .results_per_page').unbind().change(function(){
				$(this).siblings('.custom-dropdown').find('p').html($(listID+' .custom-dropdown-holder select option:selected').text());
				// reset the list
				Strut.Common.Paginator.Paginations[listID].offset = 0;
				Strut.Common.Paginator.Paginations[listID].limit = $(this).val();
				Strut.Common.Paginator.LoadList(listID);
			});
			$(listID+' .search-btn').unbind('click').click(function(){
				Strut.Common.Paginator.LoadList(listID);
			});
			$(listID+' .search_field').unbind('keydown').keydown(function(event){
				if(event.which == 13){
					$(this).parent().find('.search-btn').click();
				}
			});
			Strut.Common.Paginator.LoadList(listID);
		},
		LoadList: function(listID){
			$(listID+' .spinner').show();
			var extra = Strut.Common.Paginator.Paginations[listID].extras || "";
			$.ajax({
				url: Strut.Common.BaseURL+Strut.Common.Paginator.Paginations[listID].dataSource+"/"+extra,
				type: "POST",
				dataType: 'json',
				data: {
					column: Strut.Common.Paginator.Paginations[listID].column,
					direction: Strut.Common.Paginator.Paginations[listID].direction,
					search: $(listID+' .search_field').val(), 
					offset: Strut.Common.Paginator.Paginations[listID].offset, 
					limit: Strut.Common.Paginator.Paginations[listID].limit,
					listID: listID
				},
				success: Strut.Common.Paginator.OnListLoad
			});
		},
		OnListLoad: function(obj){
			if(!('listID' in obj))
				return;			

			var listID = obj.listID;
			// set pagination thingers
			Strut.Common.Paginator.Paginations[listID].currentPage = obj.currentPage;
			Strut.Common.Paginator.Paginations[listID].totalPages = obj.pages;

			var currPage = Strut.Common.Paginator.Paginations[listID].currentPage;
			var totalPages = Strut.Common.Paginator.Paginations[listID].totalPages;

			var prior_pages = (currPage > totalPages - 3)?(4 - (totalPages - currPage)):(2);
			var min_page = (currPage- prior_pages > 0)?(currPage - prior_pages) : (1);
			var after_pages = (currPage < 3)?(5 - currPage) : (2);
			var max_page = (currPage + after_pages < totalPages)?(currPage + after_pages) : (totalPages);

			$(listID+' .pagination').html('<li><a href="javascript:;" class="prev">&lt;</a></li>');
			if(min_page > 1){
				$(listID+' .pagination').append('<li><a href="javascript:;">1</a></li><li><a href="javascript:;" class="no-click">&hellip;</a></li>');
			}
			for(min_page; min_page <= max_page; min_page++){
				if(min_page == currPage)
					$(listID+' .pagination').append('<li class="active"><a href="javascript:;">'+min_page+'</a></li>');
				else
					$(listID+' .pagination').append('<li><a href="javascript:;">'+min_page+'</a></li>');
			}
			if(max_page < totalPages){
				$(listID+' .pagination').append('<li><a href="javascript:;" class="no-click">&hellip;</a></li><li><a href="#">'+totalPages+'</a></li>');
			}
			$(listID+' .pagination').append('<li><a href="javascript:;" class="next">&gt;</a></li>');


			$(listID+' .pagination .prev').unbind().click(function(){
				if(parseInt(Strut.Common.Paginator.Paginations[listID].offset) == 0)
					return false;
				Strut.Common.Paginator.Paginations[listID].offset = parseInt(Strut.Common.Paginator.Paginations[listID].offset) - parseInt(Strut.Common.Paginator.Paginations[listID].limit);
				Strut.Common.Paginator.LoadList(listID);
				return false;
			});
			$(listID+' .pagination .next').unbind().click(function(){
				if(parseInt(currPage) == parseInt(totalPages))
					return false;

				Strut.Common.Paginator.Paginations[listID].offset = parseInt(Strut.Common.Paginator.Paginations[listID].offset) + parseInt(Strut.Common.Paginator.Paginations[listID].limit);
				Strut.Common.Paginator.LoadList(listID);

				return false;
			});
			$(listID+' .pagination a:not(.prev, .next, .no-click)').unbind().click(function(){
				Strut.Common.Paginator.Paginations[listID].offset = parseInt($(this).text()) * parseInt(Strut.Common.Paginator.Paginations[listID].limit) - parseInt(Strut.Common.Paginator.Paginations[listID].limit);
				Strut.Common.Paginator.LoadList(listID);
				return false;
			});
			// end pagination

			$(listID+" .main-list").html( obj.html );
			$(listID+" .spinner").hide();

			Insynergi.General.init_delete_box();

			// Swap the sort order class of the <th> that corresponds with the currently sorted column
			var currentSortColumn = $('#' + Strut.Common.Paginator.Paginations[listID].column);
			if(currentSortColumn.length > 0 && currentSortColumn.attr('class') !== 'ignore') {
				if(Strut.Common.Paginator.Paginations[listID].direction == currentSortColumn.attr('class') ) {
					var currentSort = currentSortColumn.attr('class');
					currentSortColumn.attr('class', '');
					if(currentSort == 'desc') {
						currentSortColumn.addClass('asc');
					} else {
						currentSortColumn.addClass('desc');
					}
				}
			}

			$(listID+" th").unbind().click(function () {
				if($(this).attr("rel") == "ignore")
					return false;

				Strut.Common.Paginator.Paginations[listID].column = $(this).attr("id");
				Strut.Common.Paginator.Paginations[listID].direction = $(this).attr("class");
				Strut.Common.Paginator.LoadList(listID);
			});

			if(typeof Strut.Common.Paginator.Paginations[listID].callback == 'function')
				Strut.Common.Paginator.Paginations[listID].callback();

			// clear memory (IE FIX)
			obj = null;
		}
	},
	Color: {
		ToRGB: function(uintColor){
			var red = (uintColor >> 16) & 0xFF;
			var green = (uintColor >> 8) & 0xFF;
			var blue = (uintColor >> 0) & 0xFF;
			return "#"+red.toString(16)+green.toString(16)+blue.toString(16);
		}
	},

	Template: {
		Templates: {},

		Init: function(){
			Strut.Common.Template.LoadTemplates($('body'));
		},

		_parseName: function(name) {
			var tmpName = name.toLowerCase();
			if(tmpName.endsWith('-template')){
				tmpName = tmpName.substr(0,tmpName.length-('-template').length);
			}
			return tmpName;
		},

		LoadTemplates: function(location){
			location.find('.template, template').each(function(){
				Strut.Common.Template.AddTemplate($(this).attr('id'), $(this).detach());
			});
		},

		GetTemplate: function(name){
			return Strut.Common.Template.Templates[Strut.Common.Template._parseName(name)];
		},

		HasTemplate: function(name){
			return (Strut.Common.Template._parseName(name) in Strut.Common.Template.Templates);
		},

		GetTemplateClone: function(name){

			if(! Strut.Common.Template.HasTemplate(name)){
				throw new Error("Unable to find the requested template: "+name);
			}

			var tmpName = Strut.Common.Template._parseName(name);

            if(Strut.Common.Template.Templates[tmpName].hasClass('template'))
				return Strut.Common.Template.Templates[tmpName].children().clone();
			else if(Strut.Common.Template.Templates[tmpName].is('template'))
				return $(Strut.Common.Template.Templates[tmpName].html()).clone();
			else
				return Strut.Common.Template.Templates[tmpName].clone();
				
		},

		AddTemplate: function(name, view){
			Strut.Common.Template.Templates[Strut.Common.Template._parseName(name)] = view;
		}
	},

	Form: {
		
		Init: function(){
			$('form.builder-form .success').hide();

			//place holders are not supported, so do backwards compatibility support in here
			if(!$.support.placeholder){
				$.each($('input[type="text"], textarea'), function(){
					if($(this).attr('placeholder') != ''){
						$(this).val($(this).attr('placeholder'));
					}
				});
				// Clear value on focus, reinstantiate placeholder (if needed) on focusout
				// (I copy placeholder into value for IE8 support)
				$('input[type="text"], textarea').focus(function(){
					if($(this).attr('placeholder') != ''){
						if($(this).val() == $(this).attr('placeholder'))
							$(this).val('');
					}
				});
				$('input[type="text"], textarea').focusout(function(){
					if($(this).attr('placeholder') != ''){
						if($(this).val() == '')
							$(this).val($(this).attr('placeholder'));
					}
				});
				$('input[type="password"]').focusout(function(){
					if($(this).val() == '')
						$(this).removeAttr('style');
					else
						$(this).css('background','#fff');
				});
				$('textarea').focus(function(){
					if($(this).attr('placeholder') !== undefined)
						Strut.Common.ClearPlaceholder($(this));
				});

				$('textarea').focusout(function(){
					if($(this).attr('placeholder') !== undefined)
						Strut.Common.ReplacePlaceholder($(this));
				});
			} else {
				$('input[type="password"]').css('background','#fff');
			}

			$('body').on('keyup','[data-counter]', function(event){
				var limit = $(this).data('counter');
				if($(this).is('div')){
					var length = $(this).text().length;

					if(length > limit){
						$(this).text($(this).text().substr(0, limit));
					}
					length = $(this).text().length;
				} else {
					var length = $(this).val().length;

					if(length > limit){
						$(this).val($(this).val().substr(0, limit));
					}
					length = $(this).val().length;
				}
				$(this).next('.char-count').html(limit-length);
			});

			$('body').on('keyup','[data-phone]', Strut.Common.Form.PhoneKeyUp);
			$('body').on('keyup','[data-postal-code]', Strut.Common.Form.PostalKeyUp);
			$('body').on('keyup','[data-postal-zip-code]', Strut.Common.Form.PostalZipKeyUp);

			$('body').on('click','.add-file', function(){
				var fileID = $(this).attr('data-file-id');
				var origInput = $('#'+fileID);
				var newInput = origInput.clone();
				var name = origInput.attr('name');
				var existingCount = $('input[name="'+name+'"]').length;

				if(existingCount > 1)
					origInput = $('#'+fileID+'-'+(existingCount-1));
				
				newInput.val('');
				newInput.attr('id',fileID+'-'+existingCount)
				origInput.after(newInput);
			});

			$('.builder-form').unbind('submit').submit(function(e){
				// This will check if the honeypot field has been filled in.
				// It will still show the success form, but doesn't actually submit
                var form = $(this);
				if(form.find('#test-email').val() != ""){
					Strut.Common.Form.Success({},undefined,undefined,form);
					return false;
				}
				form.find('button[type=submit]').attr('disabled',true);
				if(Strut.Common.Form.Validate(form)){
					if(form.attr('data-ajax')){
						var options = {
							success: Strut.Common.Form.Success,
                            error: function(jqXHR, textStatus, errorThrown){
                                console.error(errorThrown);
                                form.find('.errors').html('An unknown error has occurred. Please contact an administrator.');
                            },
							dataType: 'json'
						};

						form.ajaxSubmit(options);
						e.preventDefault();
						return false;
					}
				} else {
					form.find('button[type=submit]').removeAttr('disabled');
					return false;
				}
			});
			
			
		},

		Validate: function(form){
			var valid = true;
			var errors = [];
			form.find('.error').removeClass('error');
			form.find('[data-required="true"]').each(function(){
				var type = $(this).attr('type');
				var currentValid = true;
				if(type == 'radio'){
					var name = $(this).attr('name');
					var radioSelected = form.find('input[name="'+name+'"]:checked').val();
					if(radioSelected === undefined)
						currentValid = false;
				} else if(type == 'checkbox') {
					currentValid = $(this).prop('checked');
				} else {
					if(!$.support.placeholder){
						if ($(this).val() == $(this).attr('placeholder')) {
							$(this).val('');
						}
					}
					if($(this).val() == ""){
						currentValid = false;
					}
				}

				if(!currentValid){
					valid = false;
					errors.push(Strut.Common.Form._SetError($(this),'Required'));
				}

			});

			form.find('[data-email]').each(function(){
				if(!Strut.Common.Form.ValidateEmail($(this).val())){
					valid = false;
					errors.push(Strut.Common.Form._SetError($(this),'Invalid Email'));
				}
			});

			form.find('[data-phone]').each(function(){
				if(!Strut.Common.Form.ValidatePhone($(this).val())){
					valid = false;
					errors.push(Strut.Common.Form._SetError($(this),'Invalid Phone'));
				}
			});

			form.find('[data-postal-code]').each(function(){
				if(!Strut.Common.Form.ValidatePostalCode($(this).val())){
					valid = false;
					errors.push(Strut.Common.Form._SetError($(this),'Invalid Postal Code'));
				}
			});
			
			form.find('[data-postal-zip-code]').each(function(){
				var firstChar = $(this).val()[0];
				if(firstChar != undefined && firstChar != " " && isNaN(firstChar)){
					if(!Strut.Common.Form.ValidatePostalCode($(this).val())){
						valid = false;
						errors.push(Strut.Common.Form._SetError($(this),'Invalid Postal Code'));
					}
				} else {
					if(!Strut.Common.Form.ValidateZipCode($(this).val())){
						valid = false;
						errors.push(Strut.Common.Form._SetError($(this),'Invalid ZIP Code'));
					}
				}
			});

			form.find('[data-regex]').each(function(){
                var regexResult = Strut.Common.Form.ValidateRegex($(this).val(), $(this).attr('data-regex'));
                if(regexResult == 'invalid regex'){
                    valid = false;
                    console.error('Invalid regex supplied');
                    errors.push(Strut.Common.Form._SetError($(this), 'Invalid Regex'));
                } else if(regexResult !== true){
					valid = false;
					errors.push(Strut.Common.Form._SetError($(this),'Invalid Format'));
				}
			});

			if(!valid){
				if(form.attr('data-validation-type') == 'Inline'){
					Strut.Common.Form.DisplayInlineErrors(errors, form);
				} else {
					Strut.Common.Form.DisplayBlockErrors(errors, form);
				}
			}

			if(form.attr('data-validation')){
				var args = [errors, form];
				customValid = Strut.Common.ExecuteFunction(form.attr('data-validation'), window, errors, form);
				if(!customValid)
					valid = customValid;
			}

			if(!valid){
				return false;
			}

			return true;
		},

		_SetError: function(field, errorType){
			field.addClass('error');
			var displayName = Strut.Common.Form._GetFieldName(field);
			var error = {};
			error['DisplayName'] = displayName;
			error['Type'] = errorType;
			error['Field'] = field;
			return error;
		},

		_GetFieldName: function(field){
			var displayName = field.attr('name');
			var label = $('label[for="'+field.attr('id')+'"]');
			if(label.length > 0)
				displayName = label.text();
			return displayName;
		},

		DisplayInlineErrors: function(errors, form){
			$('.error-message').remove();
			$.each(errors, function(key, value){
				var fieldID = value.Field.attr('id');
				var errorDiv = $('<div class="error-message"></div>');
				errorDiv.attr('data-field-id',fieldID);
				var message = Strut.Common.Form.GetErrorMessage(value.Type, 'This field');
				errorDiv.append('<p>'+message+'</p>');
				value.Field.after(errorDiv);
			});
		},

		DisplayBlockErrors: function(errors, form){
			var errorBox = form.find('.errors');
			errorBox.empty();
			if(typeof errors == 'object'){
				$.each(errors, function(key, value){
					var message = Strut.Common.Form.GetErrorMessage(value.Type, value.DisplayName);
					errorBox.append('<span>'+message+'</span>');
				});
			} else 
				errorBox.append('<span>'+errors+'</span>');
		},

		GetErrorMessage: function(type, fieldName){
			switch(type){
				case 'Required':
					return fieldName+" is required.";
					break;
				case 'Invalid Email':
					return fieldName+" must be a valid email.";
					break;
				case 'Invalid Phone':
					return fieldName+" must be a valid phone number.";
					break;
				case 'Invalid Postal Code':
					return fieldName+" must be a valid postal code.";
					break;
				case 'Invalid ZIP Code':
					return fieldName+" must be a valid ZIP code.";
					break;
				case 'Invalid Format':
					return fieldName+" must be the required format.";
					break;
                case 'Invalid Regex':
                    return "The form could not be submitted. Please contact an administrator.";
                    break;
			}
		},

		Success: function(responseText, statusText, xhr, form){
			if(Strut.Common.ValidateResult(responseText, 
				function(data){
					if("Error" in data){
						Strut.Common.Form.DisplayBlockErrors(data.Error.Message, form);
					} else if ("Location" in data) {
						Strut.Common.Redirect(data['Location']);
					}
				}, false, true)
			){
				form.find('.errors').hide();
				form.find('.success').show();

				return false;
			} else {
				form.find('button[type=submit]').removeAttr('disabled');
			}
		},

		ClearForm: function(form) {
			form.get(0).reset();  // clear the form

			// Now reset any fake dropdowns to their default value
			var fakeDropdowns = form.find('.fake-dropdown');
			fakeDropdowns.each(function() {
				var defaultText = $(this).closest('.custom-dropdown').find('select option:first').text();
				$(this).find('[data-value]').html(defaultText);
			});
		},

		PhoneKeyUp: function(event){
			if (event.keyCode != 8 &&  event.keyCode != 46 &&  event.keyCode != 37 &&  event.keyCode != 39) {
				var v = Strut.Common.Form.FormatPhoneNumber($(this).val());
				
				$(this).val(v);
			}
		},
		FormatPhoneNumber: function(num){
			var v = '';
			num = num.replace(/\D/g,'');
			if(num.length > 0 && num.length+1 <= 4){
				v = '('+num;
			}
			if(num.length+1 > 4 && num.length+1 <= 7){
				v = '('+num.substring(0,3)+') '+num.substring(3);
			}
			if(num.length+1 > 7 && num.length+1 <= 11){
				v = '('+num.substring(0,3)+') '+num.substring(3,6)+'-'+num.substring(6);
			}
			if(num.length+1 > 11){
				v = '('+num.substring(0,3)+') '+num.substring(3,6)+'-'+num.substring(6,10)+'(x)'+num.substring(10);
			}
			return v;
		},

		PostalKeyUp: function(event){
			if (event.keyCode != 8 &&  event.keyCode != 46 &&  event.keyCode != 37 &&  event.keyCode != 39) {
				var v = Strut.Common.Form.FormatPostalCode($(this).val());
				
				$(this).val(v);
			}
		},
		
		PostalZipKeyUp: function(event){
			var firstChar = $(this).val()[0];
			if(firstChar != undefined && firstChar != " " && isNaN(firstChar)){
				if(event.keyCode != 8 &&  event.keyCode != 46 &&  event.keyCode != 37 &&  event.keyCode != 39) {
					var v = Strut.Common.Form.FormatPostalCode($(this).val());
					
					$(this).val(v);
				}
			} else {
				if(event.keyCode != 8 &&  event.keyCode != 46 &&  event.keyCode != 37 &&  event.keyCode != 39) {
					var v = Strut.Common.Form.FormatZipCode($(this).val());
					
					$(this).val(v);
				}
			}
		},
		
		FormatPostalCode: function(postal){
			postal = postal.replace(' ','');
			postal = postal.toUpperCase();
			if(postal.length > 6)
				postal = postal.substring(0, 6);
			if(postal.length > 3)
				postal = postal.substring(0, 3) + " " + postal.substring(3);

			postal = postal.toUpperCase();
			return postal;
		},
		
		FormatZipCode: function(zip){
			zip = zip.replace(/\D/g,'');
			zip = zip.toUpperCase();
			if(zip.length > 9)
				zip = zip.substring(0, 9);
			if(zip.length > 5)
				zip = zip.substring(0, 5) + "-" + zip.substring(5);

			return zip;
		},

		ValidateEmail: function(email){
			// RFC 2822 email address
			var sQtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
			var sDtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
			var sAtom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
			var sQuotedPair = '\\x5c[\\x00-\\x7f]';
			var sDomainLiteral = '\\x5b(' + sDtext + '|' + sQuotedPair + ')*\\x5d';
			var sQuotedString = '\\x22(' + sQtext + '|' + sQuotedPair + ')*\\x22';
			var sDomain_ref = sAtom;
			var sSubDomain = '(' + sDomain_ref + '|' + sDomainLiteral + ')';
			var sWord = '(' + sAtom + '|' + sQuotedString + ')';
			var sDomain = sSubDomain + '(\\x2e' + sSubDomain + ')*';
			var sLocalPart = sWord + '(\\x2e' + sWord + ')*';
			var sAddrSpec = sLocalPart + '\\x40' + sDomain; // complete RFC822 email address spec
			var sValidEmail = '^' + sAddrSpec + '$'; // as whole string

			var reValidEmail = new RegExp(sValidEmail);

			return Strut.Common.Form.ValidateRegex(email, reValidEmail);
		},

		ValidatePhone: function(phone){
			// var regEx = /^((\(\d{3}\) \d{3}\-\d{4}$)|(\(\d{3}\) \d{3}\-\d{4}\ \d{0,4}$))/;
			// var regEx = /^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/;

			//if(regEx.test(phone)){
			if(phone.length >= 14 || phone == ''){
				return true;
			} else {
				return false;
			}
		},

		ValidatePostalCode: function(postal){
			var postalRegEx = new RegExp(/[ABCEGHJKLMNPRSTVXYabceghjklmnprstvxy][0-9][ABCEGHJKLMNPRSTVWXYZabceghjklmnprstvwxyz](-| |)[0-9][ABCEGHJKLMNPRSTVWXYZabceghjklmnprstvwxyz][0-9]$/);

			return Strut.Common.Form.ValidateRegex(postal, postalRegEx);
		},
		
		ValidateZipCode: function(zip){
			var zipRegEx = new RegExp(/(^\d{5}$)|(^\d{5}-\d{4}$)/);

			return Strut.Common.Form.ValidateRegex(zip, zipRegEx);
		},

		ValidateRegex: function(value, regex)
		{
			if(typeof regex != 'object'){
				var flags = regex.replace(/.*\/([gimy]*)$/, '$1');
                var pattern = regex.replace(new RegExp('^/(.*?)/'+flags+'$'), '$1');
                try {
                    regex = new RegExp(pattern, flags);
                }
                catch(e){
                    return 'invalid regex';
                }
			}
			if(regex.test(value) || value == '')
				return true

			return false;
		}
	},
	AjaxDownload: function(url, data) {
		var $iframe,
			iframe_doc,
			iframe_html;

		if (($iframe = $('#download_iframe')).length === 0) {
			$iframe = $("<iframe id='download_iframe'" +
						" style='display: none' src='about:blank'></iframe>"
					   ).appendTo("body");
		}

		iframe_doc = $iframe[0].contentWindow || $iframe[0].contentDocument;

		if (iframe_doc.document) {
			iframe_doc = iframe_doc.document;
		}

		iframe_html = "<html><head></head><body><form method='POST' action='" + url +"'>" 

		Object.keys(data).forEach(function(key){
			iframe_html += "<input type='hidden' name='"+key+"' value='"+data[key]+"'>";
		});

		iframe_html +="</form></body></html>";

		iframe_doc.open();
		iframe_doc.write(iframe_html);
		$(iframe_doc).find('form').submit();
	},
	PostLoader:{
		_Callbacks: [],
		Add: function(callback){
			Strut.Common.PostLoader._Callbacks.push(callback);
		},
		Load: function(){	
			//for (var i = 0, len = Strut.Common.PostLoader._Callbacks.length; i < len; i++) {
			while(Strut.Common.PostLoader._Callbacks.length > 0){
				if(typeof Strut.Common.PostLoader._Callbacks[0] == 'function'){
					Strut.Common.PostLoader._Callbacks[0].call(document);
				}
				Strut.Common.PostLoader._Callbacks.shift();
			};
		}
	}

};

$.fn.selectTextRange = function(start, end){
	if(!end) end = start; 
	return this.each(function(){
	    if(this.setSelectionRange){
	        this.focus();
	        this.setSelectionRange(start, end);
	    } else if (this.createTextRange){
	        var range = this.createTextRange();
	        range.collapse(true);
	        range.moveEnd('character', end);
	        range.moveStart('character', start);
	        range.select();
	    }
	});
}

//PLACEHOLDER SUPPORT DETECTION
$.support.placeholder = (function() {
	test = document.createElement('input');
	return ('placeholder' in test);
})();
// use if(!$.support.placeholder){} for placeholder support


$.support.isMobile = (function(){
	var mobileRegEx = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i;
	return mobileRegEx.test(navigator.userAgent);
})();

$.support.isIE = (function(){
	var UA = window.navigator.userAgent;
	return (UA.indexOf('MSIE') != -1);
})();

// ES 15.2.3.6 Object.defineProperty ( O, P, Attributes )
// Partial support for most common case - getters, setters, and values
(function() {
  if (!Object.defineProperty ||
      !(function () { try { Object.defineProperty({}, 'x', {}); return true; } catch (e) { return false; } } ())) {
    var orig = Object.defineProperty;
    Object.defineProperty = function (o, prop, desc) {
      // In IE8 try built-in implementation for defining properties on DOM prototypes.
      if (orig) { try { return orig(o, prop, desc); } catch (e) {} }

      if (o !== Object(o)) { throw TypeError("Object.defineProperty called on non-object"); }
      if (Object.prototype.__defineGetter__ && ('get' in desc)) {
        Object.prototype.__defineGetter__.call(o, prop, desc.get);
      }
      if (Object.prototype.__defineSetter__ && ('set' in desc)) {
        Object.prototype.__defineSetter__.call(o, prop, desc.set);
      }
      if ('value' in desc) {
        o[prop] = desc.value;
      }
      return o;
    };
  }
}());


// ES5 15.4.4.18 Array.prototype.forEach ( callbackfn [ , thisArg ] )
// From https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Array/forEach
if (!Array.prototype.forEach) {
  Array.prototype.forEach = function (fun /*, thisp */) {
    if (this === void 0 || this === null) { throw TypeError(); }

    var t = Object(this);
    var len = t.length >>> 0;
    if (typeof fun !== "function") { throw TypeError(); }

    var thisp = arguments[1], i;
    for (i = 0; i < len; i++) {
      if (i in t) {
        fun.call(thisp, t[i], i, t);
      }
    }
  };
}

//add a clone function to arrays - doesn't exist in any browser, not a polyfill
// Array.prototype.clone = function() {
// 	return this.slice(0);
// };
Object.defineProperty(Array.prototype, 'clone', {
	enumerable: false,
	configurable: false,
	writable: false,
	value: function () {
		return this.slice(0);
	}
});

// String.prototype.ucFirst = function(){
// 	return this.charAt(0).toUpperCase() + this.substring(1);
// };
Object.defineProperty(String.prototype, 'ucFirst', {
	enumerable: false,
	configurable: false,
	writable: false,
	value: function () {
	  return this.charAt(0).toUpperCase() + this.substring(1);
	}
});
Object.defineProperty(String.prototype, 'ucWords', {
	enumerable: false,
	configurable: false,
	writable: false,
	value: function () {
	  return this.replace(/\w\S*/g,function(str){
		return str.ucFirst();
	  });
	}
});

Object.defineProperty(Array.prototype, 'intersect', {
	enumerable: false,
	configurable: false,
	writable: false,
	value: function(){
		var allArrays = Array.prototype.slice.call(arguments);
		allArrays.unshift(this);
		allArrays.sort(function(a,b){ return a.length - b.length; });
		return allArrays.shift().reduce(function(res,v){
			if(res.indexOf(v) === -1 && allArrays.every(function(a){ return a.indexOf(v) !== -1; }))
				res.push(v);
			return res;
		},[]);
	}
});

Object.defineProperty(Array.prototype, 'intersectWithCmp', {
	enumerable: false,
	configurable: false,
	writable: false,
	value: function(){
		var allArrays = Array.prototype.slice.call(arguments);
		var cmp = allArrays.pop();
		allArrays.unshift(this);
		allArrays.sort(function(a,b){ return a.length - b.length; });
		return allArrays.shift().reduce(function(res,v){
			if(res.indexOfWithCmp(v,0,cmp) === -1 && allArrays.every(function(a){ return a.indexOfWithCmp(v,0,cmp) !== -1; }))
				res.push(v);
			return res;
		},[]);
	}
});

Object.defineProperty(Array.prototype, 'indexOfWithCmp',{
	enumerable: false,
	configurable: false,
	writable: false,
	value: function(searchElement, fromIndex, cmp){
		var k;
		if (this == null) {
			throw new TypeError('"this" is null or not defined');
		}
		var O = Object(this);
		var len = O.length >>> 0;
		if (len === 0) {
			return -1;
		}
		var n = +fromIndex || 0;
		if (Math.abs(n) === Infinity) {
			n = 0;
		}
		if (n >= len) {
			return -1;
		}
		k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);
		while (k < len) {
			var kValue;
			if (k in O && cmp(searchElement,O[k])) {
				return k;
			}
			k++;
		}
		return -1;
	}
});

//string startswith polyfill
if (!String.prototype.startsWith) {
  Object.defineProperty(String.prototype, 'startsWith', {
	enumerable: false,
	configurable: false,
	writable: false,
	value: function (searchString, position) {
	  position = position || 0;
	  return this.indexOf(searchString, position) === position;
	}
  });
}

if (!String.prototype.endsWith) {
  Object.defineProperty(String.prototype, 'endsWith', {
	enumerable: false,
	configurable: false,
	writable: false,
	value: function (searchString, position) {
	  var subjectString = this.toString();
      if (position === undefined || position > subjectString.length) {
          position = subjectString.length;
      }
      position -= searchString.length;
      var lastIndex = subjectString.indexOf(searchString, position);
      return lastIndex !== -1 && lastIndex === position;
	}
  });
}


if(!Math.distance){
	Math.distance = function(x0,y0,x1,y1){
		return Math.sqrt((x0 -= x1) * x0 + (y0 -= y1) * y0);
	};
}

if(!Math.trunc){
	Math.trunc = function(x) {
		return x < 0 ? Math.ceil(x) : Math.floor(x);
	};
}


//The below is a fix for object.keys missing in < ie9
if (!Object.keys) {
  Object.keys = function(obj) {
	var keys = [];
	for (var i in obj) {
	  if (obj.hasOwnProperty(i)) {
		keys.push(i);
	  }
	}
	return keys;
  };
}



// EnergyIQ.Members = {
// 	TestFunc: function(){
// 		console.log("EIQ Members"+"TestFunc");
// 	},
// };



// var Bob = Bob || {};

// Bob.Slideshow = {
// 	Init: function(){
// 		console.log("EIQ Slideshow"+"Init");
// 	},
// 	Test: function(){
// 		console.log("EIQ Slideshow"+"Test");
// 	},
// }


// $.extend(EnergyIQ,Bob);
