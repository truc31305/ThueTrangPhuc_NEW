document.addEventListener('DOMContentLoaded', () => {
	// Gallery thumbs switch
	const gallery = document.querySelector('[data-gallery]');
	if (gallery) {
		const mainImg = gallery.querySelector('[data-main-img]');
		const lens = gallery.querySelector('[data-zoom-lens]');
		gallery.querySelectorAll('[data-thumb]').forEach(thumb => {
			thumb.addEventListener('click', () => {
				gallery.querySelectorAll('[data-thumb]').forEach(t => t.classList.remove('active'));
				thumb.classList.add('active');
				mainImg.src = thumb.getAttribute('data-src');
			});
		});

		// Simple zoom on mouse move
		const main = gallery.querySelector('[data-main]');
		if (main && lens) {
			main.addEventListener('mouseenter', () => { lens.style.display = 'block'; });
			main.addEventListener('mouseleave', () => { lens.style.display = 'none'; });
			main.addEventListener('mousemove', (e) => {
				const rect = main.getBoundingClientRect();
				const x = e.clientX - rect.left; const y = e.clientY - rect.top;
				const lensSize = lens.offsetWidth / 2;
				lens.style.left = Math.max(lensSize, Math.min(rect.width - lensSize, x)) - lensSize + 'px';
				lens.style.top = Math.max(lensSize, Math.min(rect.height - lensSize, y)) - lensSize + 'px';
				mainImg.style.transformOrigin = `${x}px ${y}px`;
				mainImg.style.transform = 'scale(1.6)';
			});
			main.addEventListener('mouseleave', () => { mainImg.style.transform = 'scale(1)'; });
		}
	}

	// Mobile hamburger menu
	const toggle = document.querySelector('[data-menu-toggle]');
	const nav = document.getElementById('nav');
	if (toggle && nav) {
		toggle.addEventListener('click', () => {
			const open = nav.classList.toggle('open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			document.body.style.overflow = open ? 'hidden' : '';
		});
		// Close menu when clicking a link
		nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
			nav.classList.remove('open');
			toggle.setAttribute('aria-expanded', 'false');
			document.body.style.overflow = '';
		}));
	}

	// Falling petals animation (lightweight)
	const canvas = document.getElementById('petalCanvas');
	if (canvas) {
		const ctx = canvas.getContext('2d');
		let width, height, petals;
		// Persisted continuity across pages
		const seedKey = 'petalSeed';
		const startKey = 'petalStart';
		if (!localStorage.getItem(seedKey)) localStorage.setItem(seedKey, String(Math.random()*1e9));
		if (!localStorage.getItem(startKey)) localStorage.setItem(startKey, String(Date.now()));
		const baseSeed = Number(localStorage.getItem(seedKey));
		const startTime = Number(localStorage.getItem(startKey));
		function mulberry32(a){
			return function(){let t = a += 0x6D2B79F5; t = Math.imul(t ^ t >>> 15, t | 1); t ^= t + Math.imul(t ^ t >>> 7, t | 61); return ((t ^ t >>> 14) >>> 0) / 4294967296;}
		}
		let rand = mulberry32(baseSeed || 1);
		function resize(){
			width = canvas.width = window.innerWidth; height = canvas.height = window.innerHeight;
			const count = Math.min(80, Math.floor(width * height / 18000));
			const elapsed = (Date.now() - startTime) / 16.67; // frames since start (~60fps)
			petals = Array.from({length: count}, (_, i) => newPetal(i, elapsed));
		}
		function newPetal(i, elapsed){
			const r1 = rand(); const r2 = rand(); const r3 = rand();
			const s = 10 + r1*12;
			const speedY = 0.6 + r2*0.8;
			const speedX = -0.5 + r3*1;
			const y0 = (-20 - rand()*height) + (elapsed * speedY) % (height + 60);
			return {
				x: Math.random()*width,
				y: y0,
				s: s,
				speedY: speedY,
				speedX: speedX,
				rot: rand()*Math.PI*2,
				rotSpeed: -0.02 + rand()*0.04,
				color: `hsl(${340 + rand()*10}, 75%, ${85 + rand()*8}%)`
			};
		}
		function drawPetal(p){
			ctx.save();
			ctx.translate(p.x, p.y); ctx.rotate(p.rot);
			ctx.fillStyle = p.color; // Sakura petal
			ctx.beginPath();
			ctx.moveTo(0, -p.s/2);
			ctx.quadraticCurveTo(p.s*0.6, -p.s*0.6, p.s*0.55, -p.s*0.1);
			ctx.quadraticCurveTo(p.s*0.5, p.s*0.6, 0, p.s*0.55);
			ctx.quadraticCurveTo(-p.s*0.5, p.s*0.6, -p.s*0.55, -p.s*0.1);
			ctx.quadraticCurveTo(-p.s*0.6, -p.s*0.6, 0, -p.s/2);
			ctx.fill();
			ctx.restore();
		}
		function tick(){
			ctx.clearRect(0,0,width,height);
			for (const p of petals){
				p.y += p.speedY; p.x += p.speedX; p.rot += p.rotSpeed;
				if (p.y > height + 30) { Object.assign(p, newPetal(), { y: -20, x: Math.random()*width }); }
				drawPetal(p);
			}
			requestAnimationFrame(tick);
		}
		resize();
		window.addEventListener('resize', resize);
		tick();
	}

	// Hero slider
	const slider = document.querySelector('[data-slider]');
	if (slider){
		const slides = Array.from(slider.querySelectorAll('[data-slide]'));
		const prevBtn = slider.querySelector('[data-prev]');
		const nextBtn = slider.querySelector('[data-next]');
		const dotsWrap = slider.querySelector('[data-dots]');
		let current = 0; let timer;

		function goTo(index){
			slides[current].classList.remove('active');
			current = (index + slides.length) % slides.length;
			slides[current].classList.add('active');
			if (dotsWrap){
				Array.from(dotsWrap.children).forEach((d,i)=>d.classList.toggle('active', i===current));
			}
		}

		function next(){ goTo(current + 1); }
		function prev(){ goTo(current - 1); }

		function start(){ stop(); timer = setInterval(next, 4000); }
		function stop(){ if (timer) clearInterval(timer); }

		// Build dots
		if (dotsWrap){
			dotsWrap.innerHTML = '';
			slides.forEach((_, i) => {
				const b = document.createElement('button');
				b.type = 'button';
				if (i===0) b.classList.add('active');
				b.addEventListener('click', ()=>{ goTo(i); start(); });
				dotsWrap.appendChild(b);
			});
		}

		// Bind controls
		if (prevBtn) prevBtn.addEventListener('click', ()=>{ prev(); start(); });
		if (nextBtn) nextBtn.addEventListener('click', ()=>{ next(); start(); });

		slider.addEventListener('mouseenter', stop);
		slider.addEventListener('mouseleave', start);

		start();
	}

	// Auth dropdown
	const authToggle = document.querySelector('[data-auth-toggle]');
	const authDropdown = document.getElementById('authDropdown');
	if (authToggle && authDropdown) {
		function open(){ authDropdown.classList.add('open'); authDropdown.setAttribute('aria-hidden','false'); }
		function close(){ authDropdown.classList.remove('open'); authDropdown.setAttribute('aria-hidden','true'); }
		authToggle.addEventListener('click', (e)=>{ e.preventDefault(); const isOpen = authDropdown.classList.contains('open'); isOpen ? close() : open(); });
		document.addEventListener('click', (e)=>{
			if (!authDropdown.contains(e.target) && !authToggle.contains(e.target)) close();
		});
		document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') close(); });

		// Prefill email from cookie if available
		const ddEmail = document.getElementById('dd-email');
		if (ddEmail) {
			const m = document.cookie.match(/(?:^|; )last_registered_email=([^;]+)/);
			if (m) ddEmail.value = decodeURIComponent(m[1]);
		}
	}

	// Password visibility toggles
	function bindPasswordToggle(container=document){
		container.querySelectorAll('[data-toggle-password]')
			.forEach(chk => {
				const target = chk.getAttribute('data-toggle-password');
				const input = container.querySelector(target);
				if (!input) return;
				chk.addEventListener('change', () => {
					input.type = chk.checked ? 'text' : 'password';
				});
			});
	}
	bindPasswordToggle();

	// Simple register form validation + submit guard
	const registerForm = document.querySelector('.register-page form.auth-form');
	if (registerForm) {
		let submitting = false;
		registerForm.addEventListener('submit', (e) => {
			if (submitting) { e.preventDefault(); return; }
			const name = registerForm.querySelector('#name');
			const phoneOrEmail = registerForm.querySelector('#phoneOrEmail');
			const pass = registerForm.querySelector('#password');
			const confirm = registerForm.querySelector('#confirm');
			let msg = '';
			const val = (el)=> (el && typeof el.value==='string') ? el.value.trim() : '';
			const id = val(phoneOrEmail);
			if (!val(name)) msg = 'Vui lòng nhập tên tài khoản';
			else if (!id) msg = 'Vui lòng nhập số điện thoại hoặc email';
			else if (id.includes('@')) {
				const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(id);
				if (!ok) msg = 'Email không hợp lệ';
			} else {
				const digits = id.replace(/\D+/g,'');
				if (digits.length < 9) msg = 'Số điện thoại không hợp lệ';
			}
			if (!msg && val(pass).length < 6) msg = 'Mật khẩu tối thiểu 6 ký tự';
			if (!msg && val(pass) !== val(confirm)) msg = 'Mật khẩu xác nhận không khớp';
			if (msg) {
				e.preventDefault();
				alert(msg);
				return;
			}
			submitting = true;
		});
	}

	// Login form submit guard
	const loginForm = document.querySelector('section.auth-section form.auth-form');
	if (loginForm && !loginForm.closest('.register-page')){
		let submitting = false;
		loginForm.addEventListener('submit', (e)=>{
			if (submitting) { e.preventDefault(); return; }
			submitting = true;
		});
	}
});

