

"use strict";

//===== Prealoder

window.onload = function () {
	window.setTimeout(fadeout, 500);
}

function fadeout() {
	document.querySelector('.preloader').style.opacity = '0';
	document.querySelector('.preloader').style.display = 'none';
}


/*=====================================
Sticky
======================================= */
window.onscroll = function () {
	var header_navbar = document.querySelector(".navbar-area");
	var sticky = header_navbar.offsetTop;

	if (window.pageYOffset > sticky) {
		header_navbar.classList.add("sticky");
	} else {
		header_navbar.classList.remove("sticky");
	}



	// show or hide the back-top-top button
	var backToTo = document.querySelector(".scroll-top");
	if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
		backToTo.style.display = "block";
	} else {
		backToTo.style.display = "none";
	}
};


// section menu active
function onScroll(event) {
	var sections = document.querySelectorAll('.page-scroll');
	var scrollPos = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;

	for (var i = 0; i < sections.length; i++) {
		var currLink = sections[i];
		var val = currLink.getAttribute('href');
		var refElement = document.querySelector(val);
		var scrollTopMinus = scrollPos + 73;
		if (refElement.offsetTop <= scrollTopMinus && (refElement.offsetTop + refElement.offsetHeight > scrollTopMinus)) {
			document.querySelector('.page-scroll').classList.remove('active');
			currLink.classList.add('active');
		} else {
			currLink.classList.remove('active');
		}
	}
};

window.document.addEventListener('scroll', onScroll);


//===== close navbar-collapse when a  clicked
let navbarToggler = document.querySelector(".navbar-toggler");
var navbarCollapse = document.querySelector(".navbar-collapse");

document.querySelectorAll(".page-scroll").forEach(e =>
	e.addEventListener("click", () => {
		navbarToggler.classList.remove("active");
		navbarCollapse.classList.remove('show')
	})
);
navbarToggler.addEventListener('click', function () {
	navbarToggler.classList.toggle("active");
});



// WOW active
new WOW().init();




// count down timer
const countDownClock = (number = 100, format = 'seconds') => {

	const d = document;
	const daysElement = d.querySelector('.days');
	const hoursElement = d.querySelector('.hours');
	const minutesElement = d.querySelector('.minutes');
	const secondsElement = d.querySelector('.seconds');
	let countdown;
	convertFormat(format);


	function convertFormat(format) {
		switch (format) {
			case 'seconds':
				return timer(number);
			case 'minutes':
				return timer(number * 60);
			case 'hours':
				return timer(number * 60 * 60);
			case 'days':
				return timer(number * 60 * 60 * 24);
		}
	}

	function timer(seconds) {
		const now = Date.now();
		const then = now + seconds * 1000;

		countdown = setInterval(() => {
			const secondsLeft = Math.round((then - Date.now()) / 1000);

			if (secondsLeft <= 0) {
				clearInterval(countdown);
				return;
			};

			displayTimeLeft(secondsLeft);

		}, 1000);
	}

	function displayTimeLeft(seconds) {
		daysElement.textContent = Math.floor(seconds / 86400);
		hoursElement.textContent = Math.floor((seconds % 86400) / 3600);
		minutesElement.textContent = Math.floor((seconds % 86400) % 3600 / 60);
		secondsElement.textContent = seconds % 60 < 10 ? `0${seconds % 60}` : seconds % 60;
	}
}


/*
	start countdown
	enter number and format
	days, hours, minutes or seconds
*/
countDownClock(90, 'days');


// FAQ collapse icon toggle (change + to − when expanded)
(function () {
	var faqToggles = document.querySelectorAll('.faq-toggle');
	faqToggles.forEach(function (btn) {
		var targetSelector = btn.getAttribute('data-target');
		if (!targetSelector) return;
		var panel = document.querySelector(targetSelector);
		if (!panel) return;

		panel.addEventListener('show.bs.collapse', function () {
			var icon = btn.querySelector('.faq-icon');
			if (icon) icon.textContent = '−';
		});
		panel.addEventListener('hide.bs.collapse', function () {
			var icon = btn.querySelector('.faq-icon');
			if (icon) icon.textContent = '+';
		});
	});
})();


// Contact Form Handler
(function () {
	var contactForm = document.getElementById('contact-form');
	if (!contactForm) return;

	// Create and inject message container if it doesn't exist
	function createMessageContainer() {
		var existingContainer = document.getElementById('form-message-container');
		if (existingContainer) {
			existingContainer.remove();
		}

		var messageContainer = document.createElement('div');
		messageContainer.id = 'form-message-container';
		messageContainer.style.cssText = `
			position: fixed;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background: white;
			padding: 40px 50px;
			border-radius: 16px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
			z-index: 10000;
			max-width: 500px;
			width: 90%;
			text-align: center;
			animation: slideIn 0.3s ease-out;
		`;

		var overlay = document.createElement('div');
		overlay.id = 'form-message-overlay';
		overlay.style.cssText = `
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(0, 0, 0, 0.5);
			z-index: 9999;
			animation: fadeIn 0.3s ease-out;
		`;

		// Add animation keyframes
		if (!document.getElementById('message-animations')) {
			var style = document.createElement('style');
			style.id = 'message-animations';
			style.innerHTML = `
				@keyframes slideIn {
					from { opacity: 0; transform: translate(-50%, -45%); }
					to { opacity: 1; transform: translate(-50%, -50%); }
				}
				@keyframes fadeIn {
					from { opacity: 0; }
					to { opacity: 1; }
				}
			`;
			document.head.appendChild(style);
		}

		document.body.appendChild(overlay);
		document.body.appendChild(messageContainer);

		return { container: messageContainer, overlay: overlay };
	}

	function showMessage(message, isSuccess) {
		var elements = createMessageContainer();
		var container = elements.container;
		var overlay = elements.overlay;

		var icon = isSuccess 
			? '<div style="font-size: 60px; color: #84E4A4; margin-bottom: 20px;">✓</div>'
			: '<div style="font-size: 60px; color: #ff4444; margin-bottom: 20px;">✗</div>';

		var title = isSuccess ? 'Success!' : 'Error';
		var titleColor = isSuccess ? '#1029a6' : '#ff4444';

		container.innerHTML = `
			${icon}
			<h3 style="color: ${titleColor}; margin-bottom: 15px; font-size: 28px;">${title}</h3>
			<p style="color: #65677E; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">${message}</p>
			<button id="close-message-btn" style="
				background: linear-gradient(to right, #365ae1, #244ae2);
				color: white;
				border: none;
				padding: 12px 36px;
				border-radius: 8px;
				font-size: 16px;
				font-weight: 500;
				cursor: pointer;
				transition: all 0.3s ease;
			">OK</button>
		`;

		var closeBtn = document.getElementById('close-message-btn');
		closeBtn.addEventListener('mouseenter', function() {
			this.style.transform = 'translateY(-2px)';
			this.style.boxShadow = '0 6px 20px rgba(54, 90, 225, 0.4)';
		});
		closeBtn.addEventListener('mouseleave', function() {
			this.style.transform = 'translateY(0)';
			this.style.boxShadow = 'none';
		});

		function closeMessage() {
			container.style.animation = 'slideIn 0.2s ease-in reverse';
			overlay.style.animation = 'fadeIn 0.2s ease-in reverse';
			setTimeout(function() {
				container.remove();
				overlay.remove();
			}, 200);
		}

		closeBtn.addEventListener('click', closeMessage);
		overlay.addEventListener('click', closeMessage);
	}

	contactForm.addEventListener('submit', function (e) {
		e.preventDefault();

		// Get form data
		var formData = new FormData(contactForm);
		var submitBtn = contactForm.querySelector('button[type="submit"]');
		var originalBtnText = submitBtn.textContent;

		// Disable submit button and show loading state
		submitBtn.disabled = true;
		submitBtn.textContent = 'Sending...';

		// Send form data via AJAX
		fetch('assets/mail.php', {
			method: 'POST',
			body: formData
		})
		.then(response => response.json())
		.then(data => {
			// Reset button state
			submitBtn.disabled = false;
			submitBtn.textContent = originalBtnText;

			if (data.success) {
				// Show success message
				showMessage(data.message, true);
				// Reset form
				contactForm.reset();
				console.log('Form submitted successfully:', data);
			} else {
				// Show error message
				var errorMsg = data.message;
				if (data.errors && data.errors.length > 0) {
					errorMsg += '<br><br>' + data.errors.join('<br>');
				}
				showMessage(errorMsg, false);
				console.error('Form submission error:', data);
			}
		})
		.catch(error => {
			// Reset button state
			submitBtn.disabled = false;
			submitBtn.textContent = originalBtnText;
			showMessage('An error occurred. Please try again later.', false);
			console.error('Fetch error:', error);
		});
	});
})();