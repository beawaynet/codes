function Validation ()
{
	var self = this;

	this.server_validation = function (value, form, field, callback)
	{
		$.ajax({
			type: "POST",
			url: "/validations/" + field,
			data: {value: value},
			timeout: 3000,
			beforeSend: function (data)
			{
			},
			success: function (data)
			{
				if (data.status === undefined)
				{
					callback (true, 'global', form, '');
					return false;
				}

				if (data.status.string == 'OK')
				{
					if (data.response.status == 'ok')
					{
						callback (false, data.response.code, form, field);
					}
					if (data.response.status == 'error')
					{
						callback (true, data.response.code, form, field);
					}
				}
			},
			error: function (xhr, ajaxOptions, thrownError)
			{
				callback (true, 'global', form, '');
			},
			complete: function (data)
			{
			}
		});
	}

	this.login = function (value, form, field, callback)
	{
		if (/^\s+$/.test(value) === true || value == '')
		{
			callback (true, 'missingvalue', form, field);
			return false;
		}

		callback (false, '', form, field);
	}

	this.firstname = function (value, form, field, callback)
	{
		if (/^\s+$/.test(value) === true || value == '')
		{
			callback (true, 'missingvalue', form, field);
			return false;
		}

		callback (false, '', form, field);
	}

	this.lastname = function (value, form, field, callback)
	{
		if (/^\s+$/.test(value) === true || value == '')
		{
			callback (true, 'missingvalue', form, field);
			return false;
		}

		callback (false, '', form, field);
	}

	this.email = function (value, form, field, callback)
	{
		if (/^\s+$/.test(value) === true || value == '')
		{
			callback (true, 'missingvalue', form, field);
			return false;
		}

		if (/^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i.test(value) === false)
		{
			callback (true, 'wrong', form, field);
			return false;
		}

		self.server_validation (value, form, field, callback);
	}

	this.username = function (value, form, field, callback)
	{
		if (/^\s+$/.test(value) === true || value == '')
		{
			callback (true, 'missingvalue', form, field);

			return false;
		}

		if (/^[_]?[a-z0-9]+([_.-]{0,1}[a-z0-9])*?[a-z0-9]+[_]?$/i.test(value) === false)
		{
			callback (true, 'wrong', form, field);
			return false;
		}

		if (value.length < 4) 
		{
			callback (true, 'tooshort', form, field);
			return false;
		}

		if (value.length > 20)
		{
			callback (true, 'toolong', form, field);
			return false;
		}

		self.server_validation (value, form, field, callback);
	}

	this.password = function (value, form, field, callback)
	{
		if (/^\s+$/.test(value) === true || value == '')
		{
			callback (true, 'missingvalue', form, field);
			return false;
		}

		if (/^[a-z0-9\!\@\#\$\%\^\&\*\(\)\_\-\+\:\;\,\.]{1,}?$/i.test(value) === false)
		{
			callback (true, 'wrong', form, field);
			return false;
		}

		if (value.length < 8) 
		{
			callback (true, 'tooshort', form, field);
			return false;
		}

		if (value.length > 30)
		{
			callback (true, 'toolong', form, field);
			return false;
		}

		callback (false, '', form, field);
	}
	
	this.accept = function (value, form, field, callback)
	{
		if (value === true)
		{
			callback (false, '', form, field);
			return false;
		}

		callback (true, 'missingvalue', form, field);
	}
}

function Auth ()
{
	var self = this,
		form = {},
		function_name,
		formDOM,
		fieldsDOM;

	var _validation = new Validation;

	this.init = function (form_name)
	{
		function_name = form_name;

		if (form[form_name] === undefined)
		{
			form[form_name] = {};
			form[form_name].fields = {};

			formDOM = $('form[name="' + form_name + '"]');

			fieldsDOM = formDOM.find('input');

			$.each(fieldsDOM, function ()
			{
				var value = '';
	
				switch ($(this).attr('type'))
				{
					case 'checkbox':
						value = $(this).prop('checked');
						break;
					default:
						value = $(this).val();
				}

				form[form_name].fields[$(this).attr('name')] = {
					value: value,
					error: true
				}
			});

			formDOM.find('input').on('input', function ()
			{
				if (form[$(this).parents('form').attr('name')].fields[$(this).attr('name')].value == $(this).val() && $(this).val() != '' && form[$(this).parents('form').attr('name')].fields[$(this).attr('name')].error !== undefined)
				{
					form[$(this).parents('form').attr('name')].fields[$(this).attr('name')].error = false;
					
				}
				else
				{
					form[$(this).parents('form').attr('name')].fields[$(this).attr('name')].error = true;
					
				}
			});
		}
	}

	this.sibmit = function ()
	{
		$('.auth__error').hide();

		self[$(this).attr('name')] ();
		return false;
	}

	this.change = function ()
	{
//		console.log('sdfsfd');
		self.init ($(this).attr('name'));
	}

	this.server_auth = function ()
	{
		$.ajax({
			type: "POST",
			url: "/validations/" + function_name,
			data: form[function_name],
			timeout: 5000,
			beforeSend: function (data)
			{
				self.error_hide (function_name, 'global');
				formDOM.addClass('auth__overlay');
			},
			success: function (data)
			{
				if (data.status === undefined)
				{
					self.error_display ('', form_name, 'global');
					return false;
				}

				if (data.status.string == 'OK')
				{
					if (data.response.status == 'ok')
					{
						self[function_name] (data);
					}

					setTimeout(function ()
					{
						if (data.response.status == 'error')
						{
							if (data.response.fields !== undefined)
							{
								for (field_name in data.response.fields)
								{
									if (data.response.fields[field_name].code)
									{
										self.error_display (data.response.fields[field_name].code, function_name, field_name);
									}
								}
							}
							else
							{
								self.error_display (function_name, data.response.code);
							}

							formDOM.removeClass('auth__overlay');
						}						
					}, 500);
				}

				if (data.status.string == 'ERROR')
				{
					self.error_display ('', function_name, 'global');
					formDOM.removeClass('auth__overlay');
				}
			},
			error: function (xhr, ajaxOptions, thrownError)
			{
				self.error_display ('', function_name, 'global');
				formDOM.removeClass('auth__overlay');
			},
			complete: function (data)
			{
/*				setTimeout(function ()
				{
					formDOM.removeClass('auth__overlay');
				}, 500);
*/
			}
		});
	}

	this.error_display = function (field_name, error_code)
	{
		if (error_code != '') { error_code = '_' + error_code; }

		formDOM.find('.auth__error__' + field_name + error_code).show();
	}

	this.error_hide = function (field_name)
	{
		formDOM.find('[class*="auth__error__' + field_name + '"]').hide();
	}

	this.errors_toggle = function (error_status, error_code, form_name, field_name, auth)
	{
		self.error_hide (field_name);

		if (error_status === true)
		{
			self.error_display (field_name, error_code);

			form[form_name].fields[field_name].error = true;
		}

		if (error_status === false)
		{
			form[form_name].fields[field_name].error = false;
		}

		var errors_counter = 0;

		for (var field in form[form_name].fields)
		{
			if (form[form_name].fields[field].error !== undefined && form[form_name].fields[field].error === true) errors_counter++;
		}

		if (auth === true)
		{
			if (errors_counter == 0)
			{			
				self.server_auth ();
			}
		}
	}

	this.errors = function (error_status, error_code, form_name, field_name)
	{
		self.errors_toggle (error_status, error_code, form_name, field_name, false);
	}

	this.errors_auth = function (error_status, error_code, form_name, field_name)
	{
		self.errors_toggle (error_status, error_code, form_name, field_name, true);
	}

	this.signin = function (function_data)
	{
		var unchecked_fields = ["rememberme"];

		if (function_data === undefined)
		{
			$.each(fieldsDOM, function ()
			{
				var fieldDOM = $(this),
					value = '';

				switch (fieldDOM.attr('type'))
				{
					case 'checkbox':
						value = fieldDOM.prop('checked');
						break;
					default:
						value = fieldDOM.val();
				}

				unchecked_fields.forEach(function (element, index)
				{
					if (element != fieldDOM.attr('name'))
					{
						form[function_name].fields[fieldDOM.attr('name')].error = true;
					}
					else
					{
						delete form[function_name].fields[fieldDOM.attr('name')].error;
					}
				});

				form[function_name].fields[$(this).attr('name')].value = value;
			});

			_validation["login"] (form[function_name].fields["login"].value, function_name, "login", self.errors_auth);
			_validation["login"] (form[function_name].fields["password"].value, function_name, "password", self.errors_auth);
//console.log(print_r(form));
/*
			for (field in form[function_name].fields)
			{
				_validation.login (form[function_name].fields[field].value, function_name, field, _auth.errors_control);
			}
*/
			return false;
		}

		if (window.nextURL !== undefined)
		{
			document.location.href = window.nextURL;
		}
		else
		{
			window.location.reload();
		}
	}

	this.signup = function (function_data)
	{
		if (function_data === undefined)
		{
			$.each(fieldsDOM, function ()
			{
				var value = '';

				switch ($(this).attr('type'))
				{
					case 'checkbox':
						value = $(this).prop('checked');
						break;
					default:
						value = $(this).val();
				}

/*				if (value == '' || form[function_name].fields[$(this).attr('name')].value != value)
				{
	//				form[form_name].fields[$(this).attr('name')].error = true;
	//				_validation[$(this).attr('name')] (value, form_name, $(this).attr('name'), _auth.errors_control);
				}
*/
				form[function_name].fields[$(this).attr('name')].error = true;
				form[function_name].fields[$(this).attr('name')].value = value;
//				_validation[$(this).attr('name')] (value, form_name, $(this).attr('name'), _auth.errors_control);
			});

			for (field in form[function_name].fields)
			{
				_validation[field] (form[function_name].fields[field].value, function_name, field, self.errors_auth);
			}

			return false;
		}

		if (function_data.response.html !== undefined)
		{
			$('form[name="signup"]').html(function_data.response.html);
		}
	}

	this.restore = function (function_data)
	{
		if (function_data === undefined)
		{
			$.each(fieldsDOM, function ()
			{
				var value = '';

				switch ($(this).attr('type'))
				{
					case 'checkbox':
						value = $(this).prop('checked');
						break;
					default:
						value = $(this).val();
				}

				form[function_name].fields[$(this).attr('name')].error = true;
				form[function_name].fields[$(this).attr('name')].value = value;
			});

			for (field in form[function_name].fields)
			{
				_validation[field] (form[function_name].fields[field].value, function_name, field, self.errors_auth);
			}

			return false;
		}
	}

	this.oauth = function ()
	{
		var name = $(this).data('name'),
			redirect_uri = 'http://www.rufunder.com/oauth/' + name,
			links = {
				fb: 'https://www.facebook.com/dialog/oauth?client_id=499360576933121&scope=email,user_birthday,user_friends,user_location&display=popup&redirect_uri=' + redirect_uri,
				vk: 'http://oauth.vk.com/authorize?client_id=5211732&display=touch&scope=offline,wall,friends,email,photos&response_type=code&redirect_uri=' + redirect_uri,
				gg: 'https://accounts.google.com/o/oauth2/auth?redirect_uri=' + redirect_uri + '&response_type=code&client_id=143861685269-q4b9p3lvbijo13gu7tnsqpr137gckc5v.apps.googleusercontent.com&scope=https://www.googleapis.com/auth/plus.me https://www.googleapis.com/auth/userinfo.email',
			},
			winParams = {
				width: 700,
				height: 500
			},
			win = window.open(links[name], "Authorization", 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=' + winParams.width + ',height=' + winParams.height + ',left=' + Math.floor((screen.width - winParams.width) / 2) + ',top=' + Math.floor((screen.height - winParams.height) / 2));

		win.focus();
	}

$(document)
	.on('submit', 'form[name="signin"], form[name="signup"], form[name="restore"]', self.sibmit)
	.on('change', 'form[name="signin"], form[name="signup"], form[name="restore"]', self.change)
	.on('change', 'input[name="accept"]', function ()
	{
		_validation[$(this).attr('name')] ($(this).prop('checked'), $(this).parents('form').attr('name'), $(this).attr('name'), self.errors);
	});


}

function Project ()
{
	var self = this,
		accept = false,
		project_form = {
			className: 'project-create',
			error_className: 'project-error'
		},
		project = {},
		formDOM;

	var _dom = {
			classes: {
				notice: 'notice-top',
				notice_message: 'notice-top__message',
				form_header: 'project-form__header',
				cover: 'project-form__cover',
				cover_warning: 'project-form__cover-warning',
				cover_spin: 'project-form__cover-spin',
				cover_overlay: 'project-form__cover-overlay',
				cover_img: 'project-form__cover-img',
				cover_default: 'project-form__cover_default',
				cover_zoomer: 'cover-zoomer',
				cover_line: 'cover-zoomer__line',
				cover_zoomer_control: 'cover-zoomer__control',
				cover_circle: 'cover-zoomer__circle',
				categories: 'project-form__categories',
				сommission: 'project-form__сommission',
				social: 'project-form__social',
				rewards: 'project-form__rewards',
				reward: 'reward-form',
				add_reward: 'reward-form__add',
				remove_reward: 'reward-form__remove',
				error: 'project-form__error'
			},
			names: {
				project_name: 'project[name]',
				project_descr: 'project[descr]',
				project_goal: 'project[goal]',
				project_social: 'project[social]',
				cover: 'project[cover]',
				cover_add: 'cover-add',
				cover_remove: 'cover-remove',
				cover_move: 'change-position',
				save_button: 'save-button',
				public_button: 'public-button'
			}
		};

	this._init = function ()
	{
		tinyMCE.init({
			selector: '#mytextarea',
			height: 350,
			plugins: ["advlist autolink autosave link image lists charmap preview pagebreak spellchecker", "visualblocks visualchars code fullscreen insertdatetime media nonbreaking", "table contextmenu directionality emoticons template paste fullpage textcolor colorpicker textpattern"],
			menubar: false,
			statusbar: false,
			toolbar: 'fontsizeselect | bold italic underline | alignleft aligncenter alignright | image media link'
		});

		setTimeout(function ()
		{
			for (var key in rufunder.project)
			{
				if (key == 'category')
				{
					$('.' + _dom.classes.categories).bem('select').setVal(rufunder.project[key]);

					continue;
				}

				if (key == 'social' && rufunder.project[key] == 'true')
				{
					$('.' + _dom.classes.social).bem('checkbox').setMod('checked', true);

					continue;
				}

				$('[name="project[' + key + ']"]').val(rufunder.project[key]);
			}

			if (rufunder.project.rewards !== undefined)
			{
				var rewards = rufunder.project.rewards;

				for (var index in rewards)
				{
					var reward_template = $(rufunder.reward);

					reward_template.attr("data-uniqid", rewards[index].uniqid);

					for (var name in rewards[index])
					{

						reward_template.find('input[name="' + name + '"], textarea[name="' + name + '"]').val(rewards[index][name]);
					}

					$('.' + _dom.classes.rewards).append(reward_template);

				}
			}

			self.сommissionCalc ($('[name="' + _dom.names.project_goal + '"]').val());

		}, 10);
	}

	this.moveCover = function ()
	{
//console.log($('.' + _dom.classes.cover_circle).css('left'));

		var circlePos = 0;

		var origW,
			origH,
			maxZoom = 1.77777777777777,
			minZoom = 0,
			n,
			step_px,
			zoom,
			image = new Image ();

			image.src = $('.' + _dom.classes.cover_img).attr('src');
			image.onload = function ()
			{
				origW = image.width;
				origH = image.height;

				minZoom = $('.' + _dom.classes.cover_img).width() / origW;

				zoom = minZoom;

				n = ( maxZoom - ( (minZoom + 0.01) - 0.01 ) ) / 0.01;
				
				step_px = $('.' + _dom.classes.cover_line).width() / n;
			}

		if ($('[name="' + _dom.names.cover_move + '"]').attr('aria-pressed') == 'true')
		{
			if (circlePos == undefined) { var circlePos = 0; }
			
			$('.' + _dom.classes.cover_zoomer).show();

			$('.' + _dom.classes.cover_img)
				.css({'cursor': 'move'})
				.on('mousedown touchstart', function (e)
				{
					e.preventDefault();

var sX = e.pageX - $('.' + _dom.classes.cover_img).offset().left,
	sY = e.pageY - $('.' + _dom.classes.cover_img).offset().top;


					var pageX = e.pageX, 
						pageY = e.pageY;
					
					var vectorX,
						vectorY,
						willX,
						willY

//					var mouseStaticX = e.screenX,
					var	mouseStaticY = e.screenY,
						imgTop = $(this).position().top,
						imgLeft = $(this).position().left;

					var mouseX = e.pageX,
						mouseY = e.pageX;

					$(document).on('mousemove touchmove', function (event)
					{
						
var oX = event.pageX - sX,
	oY = event.pageY - sY;

if (oX > $('.' + _dom.classes.cover).offset().left)
{
	oX = $('.' + _dom.classes.cover).offset().left;
}

if (oX < ( $('.' + _dom.classes.cover).offset().left - ( $('.' + _dom.classes.cover_img).width() - $('.' + _dom.classes.cover).width() ) ))
{
	oX = $('.' + _dom.classes.cover).offset().left - ( $('.' + _dom.classes.cover_img).width() - $('.' + _dom.classes.cover).width() );
}


						$('.' + _dom.classes.cover_img).offset({left: oX});
						

					});
				});

			$(document).on('mouseup touchend', function ()
			{
				$(document).off('mousemove touchmove');
			});

			$('.' + _dom.classes.cover_circle)
				.on('mousedown', function (event)
				{
					var staticX = event.pageX,
						staticL = $(this).position().left;

					var pageX = event.pageX;

					$(document).on('mousemove', function (e)
					{
						var nn = (staticL - (staticX - e.pageX)) / step_px;

						var an = (minZoom + 0.01) + ( (nn * 0.01) - (1 * 0.01) );

						var step_proc = 100 / n;

						var an_proc = (0 + step_proc) + ( (nn * step_proc) - (1 * step_proc) );

						if (an < minZoom) { an = minZoom; }
						if (an > maxZoom) { an = maxZoom; }

						if (an_proc < 0) { an_proc = 0; }
						if (an_proc > 100) { an_proc = 100; }


						if (an > 1) { $('.' + _dom.classes.cover_warning).show(); } else { $('.' + _dom.classes.cover_warning).hide(); }

				var xRatio = ((origW * an) - $('.' + _dom.classes.cover).width()) / $('.' + _dom.classes.cover).width();
				var yRatio = ((origH * an) - $('.' + _dom.classes.cover).height()) / $('.' + _dom.classes.cover).height();


				var lt = (($('.' + _dom.classes.cover).width() / 2) * -xRatio);
				var tp = (($('.' + _dom.classes.cover).height() / 2) * -yRatio);

//console.log(lt);
//console.log(tp);


var w1, h1, w2, h2, dw, dh, rw, rh;

w1 = 500;
h1 = 375;

w2 = 500;
h2 = 375;

dw = (origW * an) - w2;
dh = (origH * an) - h2;

rw = dw / w1;
rh = dh / h1;

var pt = ($('.' + _dom.classes.cover).height() / 2) + -($('.' + _dom.classes.cover_img).position().top);
var pl = ($('.' + _dom.classes.cover).width() / 2) + -($('.' + _dom.classes.cover_img).position().left);
        
var xt = pt * rh;
var xl = pl * rw;

//console.log(pl);
//console.log(pl);

//var tp = xt * -1;
//var lt = xl * -1;
var tp = pt;
var lt = pl;


//				var left = ($('.' + _dom.classes.cover).width() / 2);// - $('.' + _dom.classes.cover_img).position().left,
//					tp = ($('.' + _dom.classes.cover).height() / 2);// - $('.' + _dom.classes.cover_img).position().top;
//console.log(tp);
				
//				left = Math.max(Math.min(left, $('.' + _dom.classes.cover).width()), 0);
//				tp = Math.max(Math.min(tp, $('.' + _dom.classes.cover).height()), 0);

//console.log(left * -xRatio);
//console.log(yRatio);

//				img.style.left = (left * -xRatio) + 'px';
//				img.style.top = (top * -yRatio) + 'px';

//						var left = (origW * an) / 2;
//						var top = ( $('.' + _dom.classes.cover_img).height() - origH * an) / 2;

//var lt = 0;// (origW * an) / 2;
//var tp = 0; //(origH * an) / 2;

//var l = (left * -xRatio)
//var t = (top * -yRatio);


//console.log($('.' + _dom.classes.cover).width());

						$('.' + _dom.classes.cover_circle).css({'left': an_proc + '%'});
						$('.' + _dom.classes.cover_img).css({'width': origW * an, 'height': origH * an, 'top': tp, 'left': lt});
					});
				})
				.on('mouseup', function ()
				{
					$(document).off('mousemove');
				});

			function coverResize ()
			{
				
			}

			function moveCircle ()
			{
				var action = $(this).data('action');

				switch (action)
				{
					case 'increase':
						circlePos = circlePos + 16.6667;
						break;
					case 'reduce':
						circlePos = circlePos - 16.6667;
						break;
				}

				if (circlePos === undefined || circlePos < 0) { circlePos = 0; }
				if (circlePos === undefined || circlePos > 100) { circlePos = 100; }



				$('.' + _dom.classes.cover_circle).css({'left':  circlePos + '%'});
				

			}

			$('.' + _dom.classes.cover_zoomer_control).on('click', moveCircle);
		}

		if ($('[name="' + _dom.names.cover_move + '"]').attr('aria-pressed') == 'false')
		{
			$('.' + _dom.classes.cover_img)
				.css({'cursor': 'default'})
				.off('mousedown');
			$('.' + _dom.classes.cover_reduce + ', ' + '.' + _dom.classes.cover_increase).off('click');
			
			$('.' + _dom.classes.cover_zoomer).hide();
			$('.' + _dom.classes.cover_warning).hide(); 
		}
	}

	this.addCover = function ()
	{
		var input_file = $('body').find('input[name="' + _dom.names.cover + '"]');

		if (input_file.length === 0)
		{
			input_file = $('<input/>').css({'display': 'none', 'visibility': 'hidden', 'position': 'absolute'}).prop({'type': 'file', 'name': _dom.names.cover, 'accept': 'image'}).appendTo('body');

			input_file.on(
			{
				change: function() { self._uploadCover(this.files); }
			});
		}

		input_file.click();
	}

	this.removeCover = function ()
	{
		var projectData = {};

		projectData.uniqid = rufunder.project.uniqid;

		$.ajax({
			type: "POST",
			url: '/ax/project/cover/remove',
			data: projectData,
			success: function(data)
			{
				if (data.status.string == 'OK')
				{
					$('.' + _dom.classes.cover).addClass(_dom.classes.cover_default);
					$('.' + _dom.classes.cover_img).fadeOut(200, function ()
					{
						$(this).attr({'src': ''});
					});
					$('[name="' + _dom.names.cover_remove + '"]').hide();
				}

				if (data.status.string == 'ERROR')
				{
					self.noticeShow ('error-global');
				}
			}
		});
	}

	this._coverOverlay = function (mod)
	{
		if (mod === false)
		{
			$('.' + _dom.classes.cover_overlay).remove();
			$('.' + _dom.classes.cover_spin).bem('spin').setMod('visible', false);
			$('[name="' + _dom.names.cover_add + '"]').bem('button').setMod('disabled', false);
			$('[name="' + _dom.names.cover_remove + '"]').bem('button').setMod('disabled', false);
		}

		if (mod === true)
		{
			$('.' + _dom.classes.cover).prepend(
				$('<div/>').addClass(_dom.classes.cover_overlay)
					.width($('.' + _dom.classes.cover).width())
					.height($('.' + _dom.classes.cover).height())
					);

			$('.' + _dom.classes.cover_spin).bem('spin').setMod('visible');
			$('[name="' + _dom.names.cover_add + '"]').bem('button').setMod('disabled', true);
			$('[name="' + _dom.names.cover_remove + '"]').bem('button').setMod('disabled', true);
		}
	}

	this._uploadCover = function (files)
	{
		$('.' + _dom.classes.error).hide();

		var file = files[0];

		if (!file.type.match(/image.*/))
		{
			$('.' + _dom.classes.error + '_file-format').show();

			return false;
		}

		if (file.size > 1048576*5)
		{
			$('.' + _dom.classes.error + '_file-toolarge').show();

			return false;
		}

		var reader = new FileReader ();
		reader.onload = function (e)
		{
			var image = new Image ();
			image.src = e.target.result;
			image.onload = function ()
			{
				if (image.width < 1024 || image.height < 768)
				{
					$('.' + _dom.classes.error + '_image-ratio').show();
					return false;
				}

				var fd = new FormData (),
					xhr = new XMLHttpRequest();

				xhr.onreadystatechange = function ()
				{
					if (this.readyState == 4)
					{
						if (this.status == 200)
						{
							var response = $.parseJSON(this.responseText)

							if (response.status.string == 'OK')
							{
								if (response.response.cover !== undefined)
								{
									var cover = new Image ();
									cover.src = response.response.cover.url;
									cover.onload = function ()
									{
										$('.' + _dom.classes.cover).removeClass(_dom.classes.cover_default);
										$('.' + _dom.classes.cover_img).attr({'src': response.response.cover.url}).fadeIn(200);
										$('[name="' + _dom.names.cover_remove + '"]').show();

										self._coverOverlay (false);
									}
								}
							}

							if (response.status.string == 'ERROR')
							{
								// error
								self._coverOverlay (false);
							}
						}
						else
						{
							// error
							self._coverOverlay (false);
						}
					}
				}

				fd.append('file', file, file.name);
				fd.append('uniqid', rufunder.project.uniqid);

				xhr.open("POST", "/ax/project/cover");
				xhr.send(fd);
				self._coverOverlay (true);
			}
		}

		reader.readAsDataURL(file);
	}

	this.submit = function ()
	{
		formDOM = $(this);
		formDOM.find('.' + project_form.error_className + '__global').hide();

		self[$(this).attr('name')] ();

		return false;
	}

	this.check_accept = function ()
	{
		formDOM = $(this).parents('form');

		if ($(this).prop('checked') === false)
		{
			accept = false;
			formDOM.find('.' + _dom.classes.error + '_accept').show();
		}
		else
		{
			accept = true;
			formDOM.find('.' + _dom.classes.error + '_accept').hide();
		}
	}

	this.agreementProject = function ()
	{
		if (accept === false)
		{
			formDOM.find('.' + _dom.classes.error + '_accept').show();

			return;
		}

		$.ajax({
			type: "GET",
			url: "/ax/project/agree",
			timeout: 3000,
			beforeSend: function (data)
			{
				$('.' + project_form.className).addClass('overlay');
			},
			success: function (data)
			{
				var form = $(data);

				form
					.on('input', 'input[name="name"]', function ()
					{
						if ($(this).val().length > 4)
						{
							$(this).parents('form').find('button').prop('disabled', false);
						}
						else
						{
							$(this).parents('form').find('button').prop('disabled', true);
						}
					})
					.on('submit', self.createProject);

				setTimeout(function ()
				{
					$('.' + project_form.className).html(form);
					$('.' + project_form.className).removeClass('overlay');
				}, 500);
			},
			error: function (xhr, ajaxOptions, thrownError)
			{
				formDOM.find('.' + project_form.error_className + '__global').show();
				$('.' + project_form.className).removeClass('overlay');
			}
		});
	}

	this.createProject = function ()
	{
		var projectData = {};

		projectData.name = $(this).find('input[name="name"]').val();

		$.ajax({
			type: "POST",
			url: "/ax/project/create",
			data: projectData,
			timeout: 3000,
			beforeSend: function (data)
			{
				$('.' + project_form.className).addClass('overlay');
			},
			success: function (data)
			{
				document.location.href = data.response.nextURL;
			},
			error: function (xhr, ajaxOptions, thrownError)
			{
				formDOM.find('.' + project_form.error_className + '__global').show();
				$('.' + project_form.className).removeClass('overlay');
			}
		});

		return false;
	}

	this.saveProject = function ()
	{
		var projectData = {};

		projectData.uniqid = rufunder.project.uniqid;

		$.each($('[name^="project["]'), function ()
		{
			var result = $(this).attr('name').match(/project\[(\w+)\]/i);

			projectData[result[1]] = $(this).val();

			if (result[1] == 'social')
			{
				if ($(this).is(':checked'))
				{
					projectData[result[1]] = 'true';
				}
				else
				{
					projectData[result[1]] = 'false';
				}
			}
		});

		projectData.rewards = [];

		$.each($('.' + _dom.classes.reward), function (i)
		{
			var reward = $(this);

			var uniqid = reward.data("uniqid");

			projectData.rewards[i] = {};

			reward.find('input, textarea, select').each(function ()
			{
				projectData.rewards[i]["uniqid"] = uniqid;
				projectData.rewards[i][$(this).attr('name')] = $(this).val();
			});
		});

		projectData.present = tinyMCE.get('mytextarea').getContent ();

		$.ajax({
			type: "POST",
			url: "/ax/project/edit",
			data: projectData,
			timeout: 3000,
			beforeSend: function (data)
			{
			},
			success: function (data)
			{
				if (data.status.string == 'OK')
				{
					self.noticeShow (data.response.status);
				}

				if (data.status.string == 'ERROR')
				{
					self.noticeShow ('error-global');
				}
			},
			error: function (xhr, ajaxOptions, thrownError)
			{
				self.noticeShow ('error-global');
			}
		});

	}

	this.publicProject = function ()
	{
		var button = $(this),
			projectData = {};

		projectData.uniqid = rufunder.project.uniqid;

		$.ajax({
			type: "POST",
			url: "/ax/project/moderate",
			data: projectData,
			timeout: 3000,
			beforeSend: function (data)
			{
			},
			success: function (data)
			{
				if (data.status.string == 'OK')
				{
					self.noticeShow (data.response.status);
					button.parent('div').fadeOut(100, function ()
					{
						$(this).remove();
					});
				}

				if (data.status.string == 'ERROR')
				{
					self.noticeShow ('error-global');
				}
			},
			error: function (xhr, ajaxOptions, thrownError)
			{
				self.noticeShow ('error-global');
			}
		});

	}

	this.addReward = function ()
	{
		if ($('.' + _dom.classes.rewards).find('.' + _dom.classes.reward).length < 10)
		{
			var projectData = {};

			projectData.uniqid = rufunder.project.uniqid;

			$.ajax({
				type: "POST",
				url: "/ax/project/reward/create",
				data: projectData,
				timeout: 3000,
				beforeSend: function (data)
				{
				},
				success: function (data)
				{
					if (data.status.string == 'OK')
					{
						var reward_template = $(rufunder.reward);

						reward_template.attr("data-uniqid", data.response.uniqid).css({'display': 'none', 'opacity': '0'}).appendTo('.' + _dom.classes.rewards);
						reward_template.slideDown(50, function ()
						{
							$(this).fadeTo(210, 1);
						});

						delete reward_template;

						if ($('.' + _dom.classes.reward).length >= 10)
						{
							$('.' + _dom.classes.add_reward).hide();
						}
					}

					if (data.status.string == 'ERROR')
					{
						self.noticeShow ('error');
					}
				},
				error: function (xhr, ajaxOptions, thrownError)
				{
					self.noticeShow ('error');
				}
			});
		}
	}

	this.removeReward = function ()
	{
		var reward = $(this),
			rewardData = {};

		rewardData.uniqid = $(this).parents('.' + _dom.classes.reward).data('uniqid');

		$.ajax({
			type: "POST",
			url: "/ax/project/reward/remove",
			data: rewardData,
			timeout: 3000,
			beforeSend: function (data)
			{
			},
			success: function (data)
			{
				if (data.status.string == 'OK')
				{
					reward.parents('.' + _dom.classes.reward).fadeOut(300, function ()
					{
						$(this).remove();
						if ($('.' + _dom.classes.reward).length < 10) { $('.' + _dom.classes.add_reward).show(); }
					});
				}

				if (data.status.string == 'ERROR')
				{
					self.noticeShow ('error');
				}
			},
			error: function (xhr, ajaxOptions, thrownError)
			{
				self.noticeShow ('error');
			}
		});
	}

	this.updateTizer = function ()
	{
		$('[data-name="' + $(this).attr('name') + '"]').text($(this).val());
	}

	this.noticeShow = function (status)
	{
		if ($('.' + _dom.classes.notice_message + '_' + status).length == 0) return false;

		$('.' + _dom.classes.notice_message + '_' + status).show();

		$('.' + _dom.classes.notice).fadeIn(300, function ()
		{
			setTimeout(function ()
			{
				self.noticeHide ();
			}, 4000);
		});
	}

	this.noticeHide = function ()
	{
		$('.' + _dom.classes.notice).fadeOut(300, function ()
		{
			$('.' + _dom.classes.notice_message).hide();
		});
	}

	this.goalInput = function ()
	{
		var value = $(this).val();

		if (/^[0-9]+$/i.test(value) === false) return false;
		
		self.сommissionCalc (value);
	}

	this.сommissionCalc = function (goal)
	{
		$('.' + _dom.classes.сommission).text('15');

		goal = parseInt(goal, 10);

		if (goal >= 51000 && goal <= 150000) $('.' + _dom.classes.сommission).text('13');
		if (goal >= 151000 && goal <= 500000) $('.' + _dom.classes.сommission).text('12');
		if (goal >= 501000) $('.' + _dom.classes.сommission).text('10');
	}

	this.key = function (e)
	{
		e = e || event;

		if (e.ctrlKey || e.altKey || e.metaKey) return;

		var chr = e.key;

		if (chr == null) return;

	}

	$(document)
		.on('focusout', '[name="' + _dom.names.project_name + '"], [name="' + _dom.names.project_descr + '"]', self.updateTizer)
		.on('click', '[name="' + _dom.names.cover_add + '"]', self.addCover)
		.on('click', '[name="' + _dom.names.cover_remove + '"]', self.removeCover)
		.on('click', '[name="' + _dom.names.cover_move + '"]', self.moveCover)
		.on('click', '.' + _dom.classes.add_reward, self.addReward)
		.on('click', '.' + _dom.classes.remove_reward, self.removeReward)
		.on('click', '[name="' + _dom.names.save_button + '"]', self.saveProject)
		.on('click', '[name="' + _dom.names.public_button + '"]', self.publicProject)
		.on('keydown', '[name="' + _dom.names.project_goal + '"]', self.key)
		.on('input', '[name="' + _dom.names.project_goal + '"]', self.goalInput)
		.on('change', 'input[name="agreement"]', self.check_accept)
		.on('submit', 'form[name="agreementProject"]', self.submit)
		.on('submit', 'form[name="editProject"]', self.submit)
		.on('click', '.' + _dom.classes.notice, self.noticeHide)
		.on('click', '.edit-menu__item', function ()
		{
			var section_name = $(this).data('action');
			if (!$(this).hasClass('_active'))
			{
				$('.edit-section:visible').fadeOut(150, function ()
				{
					$('[data-section="' + section_name + '"]').find('.' + _dom.classes.form_header).hide();
					$('[data-section="' + section_name + '"]').show();
					$('[data-section="' + section_name + '"]').find('.' + _dom.classes.form_header).fadeIn(300);
	
				});
			}
	
			$('.edit-menu__item').removeClass('_active');
			$(this).addClass('_active');
		});

}

function Settings ()
{
	var self = this;

	var _dom = {
			classes: {
				userpic_image: 'settings__userpic-image',
				userpic_upload: 'userpic_upload',
				userpic_remove: 'userpic_remove',
				error: 'settings__error'
			},
			names: {
				form: 'summary',
				userpic: 'userpic',
				save_button: 'save-button'
			}
		};

	this._uploadUserpic = function (files)
	{
		var file = files[0];

		if (!file.type.match(/image.*/))
		{
			$('.' + _dom.classes.error + '_userpic_fileformat').show();
			return;
		}
		else
		{
			if (file.size > 1048576*3)
			{
				$('.' + _dom.classes.error + '_userpic_filesize').show();
				return;
			}
		}

		var reader = new FileReader ();
		reader.onload = function (e)
		{
			var image = new Image ();
			image.src = e.target.result;
			image.onload = function ()
			{
				if (image.width < 200 || image.height < 200)
				{
					$('.' + _dom.classes.error + '_userpic_ratio').show();
					return false;
				}

				var fd = new FormData (),
					xhr = new XMLHttpRequest();

				xhr.onreadystatechange = function ()
				{
					if (this.readyState == 4)
					{
						if (this.status == 200)
						{
							var response = $.parseJSON(this.responseText)
	
							if (response.status.string == 'OK')
							{
								if (response.response.userpic !== undefined)
								{
									var userpic = new Image ();
									userpic.src = response.response.userpic;
									userpic.onload = function ()
									{
										$('.' + _dom.classes.userpic_image).attr({'src': response.response.userpic});
									}
								}
							}
	
							if (response.status.string == 'ERROR')
							{
							}
						}
						else
						{
							attachImage.remove();
						}
					}
				}

				fd.append('file', file, file.name);

				xhr.open("POST", "/ax/settings/userpic");
				xhr.send(fd);
			}
		}

		reader.readAsDataURL(file);
	}

	this.uploadUserpic = function ()
	{
		var input_file = $('body').find('input[name="' + _dom.names.userpic + '"]');

		if (input_file.length === 0)
		{
			input_file = $('<input/>').css({'display': 'none', 'visibility': 'hidden', 'position': 'absolute'}).prop({'type': 'file', 'name': _dom.names.cover, 'accept': 'image'}).appendTo('body');

			input_file.on(
			{
				change: function() { self._uploadUserpic(this.files); }
			});
		}

		input_file.click();
	}

	this.removeUserpic = function ()
	{
		$.ajax({
			type: "POST",
			url: '/ax/settings/remove-userpic',
			success: function(data)
			{
				if (data.status.string == 'OK')
				{
					$('.' + _dom.classes.userpic_remove).hide();
				}

				if (data.status.string == 'ERROR')
				{

				}
			}
		});
	}

	this.submit = function (event)
	{
		event.preventDefault();

		self['_' + $(this).attr('name')] ();

		return false;
	}

	this.saveSettings = function ()
	{
		
	}

	this._summary = function ()
	{
		var settingsData = {};

		$('form[name="' + _dom.names.form + '"]').find('input, textarea').each(function ()
		{
			settingsData[$(this).attr('name')] = $(this).val();
		});
/*
		$.ajax({
			type: "POST",
			url: "/ax/settings/summary",
			data: settingsData,
			timeout: 3000,
			beforeSend: function (data)
			{
			},
			success: function (data)
			{
				if (data.status.string == 'OK')
				{

				}

				if (data.status.string == 'ERROR')
				{
				}
			},
			error: function (xhr, ajaxOptions, thrownError)
			{
			}
		});
*/
	}

	$(document)
		.on('submit', 'form[name="' + _dom.names.form + '"]', self.submit)
		.on('click', '.' + _dom.classes.userpic_upload, self.uploadUserpic)
		.on('click', '.' + _dom.classes.userpic_remove, self.removeUserpic);

}

var _auth = new Auth,
	_project = new Project,
	_settings = new Settings;
/*
function move (event)
{
	console.log(event);
}
*/
$(document).ready(function ()
{
	$('form[name="signup"], form[name="signin"], form[name="restore"]').change();
	
	$('.js-oauth').on('click', _auth.oauth);

	if (window["rufunder"] !== undefined) { _project._init (); }

});
