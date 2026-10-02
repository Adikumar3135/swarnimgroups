
/* Swarnim Groups — premium page boot + guest reminder */
(function(){
  const loader=document.getElementById("swPageLoader");
  const finish=()=>{
    if(!loader)return;
    setTimeout(()=>loader.classList.add("hide"),520);
  };
  if(document.readyState==="complete") finish();
  else window.addEventListener("load",finish,{once:true});
  setTimeout(finish,2500);

  const reminder=document.getElementById("swLoginReminder");
  if(!reminder)return;

  let loggedIn=false;
  let authResolved=false;
  const close=()=>{
    reminder.classList.remove("show");
    reminder.setAttribute("aria-hidden","true");
    try{sessionStorage.setItem("swarnim-login-reminder","shown")}catch(e){}
  };
  reminder.querySelectorAll("[data-close-login]").forEach(el=>el.addEventListener("click",close));
  document.addEventListener("keydown",e=>{if(e.key==="Escape"&&reminder.classList.contains("show"))close()});

  const shown=()=>{try{return sessionStorage.getItem("swarnim-login-reminder")==="shown"}catch(e){return false}};
  if(shown())return;

  fetch("auth-status.php",{credentials:"same-origin",cache:"no-store"})
    .then(r=>r.ok?r.json():Promise.reject())
    .then(j=>{loggedIn=!!j.data?.user;authResolved=true})
    .catch(()=>{authResolved=false});

  setTimeout(()=>{
    if(authResolved&&!loggedIn&&!navigator.onLine){
      return;
    }
    if(authResolved&&!loggedIn){
      reminder.classList.add("show");
      reminder.setAttribute("aria-hidden","false");
    }
  },60000);
})();


const $ = s => document.querySelector(s), $$ = s => document.querySelectorAll(s);

const header = $("#header"), topBtn = $("#topBtn");
window.addEventListener("scroll", () => {
    header.classList.toggle("scrolled", scrollY > 25);
    topBtn.classList.toggle("show", scrollY > 600);
});
topBtn.onclick = () => scrollTo({ top: 0, behavior: "smooth" });

const menuBtn = $("#menuBtn"), mobileMenu = $("#mobileMenu");
menuBtn.onclick = () => mobileMenu.classList.toggle("open");
$$(".mobile-menu a").forEach(a => a.onclick = () => mobileMenu.classList.remove("open"));

const themeBtn = $("#themeBtn");
const saved = localStorage.getItem("swarnim-theme");
if (saved) document.documentElement.dataset.theme = saved;
themeBtn.textContent = document.documentElement.dataset.theme === "light" ? "☾" : "☼";
themeBtn.onclick = () => {
    const light = document.documentElement.dataset.theme !== "light";
    document.documentElement.dataset.theme = light ? "light" : "dark";
    localStorage.setItem("swarnim-theme", light ? "light" : "dark");
    themeBtn.textContent = light ? "☾" : "☼";
};

const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add("show") });
}, { threshold: .12 });
$$(".reveal").forEach(e => observer.observe(e));

const counters = $$("[data-count]");
let counterDone = false;
const counterObserver = new IntersectionObserver(entries => {
    if (!entries[0].isIntersecting || counterDone) return;
    counterDone = true;
    counters.forEach(el => {
        const target = +el.dataset.count;
        let n = 0; const step = Math.max(1, Math.ceil(target / 35));
        const t = setInterval(() => { n += step; if (n >= target) { n = target; clearInterval(t) } el.textContent = target === 12 ? n + "k+" : n + "+" }, 30);
    });
}, { threshold: .6 });
counterObserver.observe(counters[0]);

const words = ["Groups", "Power", "Comfort", "Trust"];
let wi = 0, ci = 0, del = false;
function type() {
    const el = $("#type"), word = words[wi];
    el.textContent = del ? word.slice(0, ci--) : word.slice(0, ci++);
    if (!del && ci > word.length) { del = true; setTimeout(type, 1000); return }
    if (del && ci < 0) { del = false; wi = (wi + 1) % words.length; ci = 0 }
    setTimeout(type, del ? 55 : 95);
}
type();

const banners = [
    ["data/banner1.png", "Power up with genuine electronics.", "Explore batteries, solar systems, cooling and smart home solutions from trusted brands."],
    ["data/banner2.png", "Smart comfort starts here.", "Bring reliable appliances and power solutions home with expert service."],
    ["data/banner3.png", "Technology that works for you.", "Discover practical electronics backed by genuine products and support."],
    ["data/banner4.png", "Big value. Real power.", "Special deals across batteries, inverters, solar and home appliances."]
];
let bi = 0;
function renderBanner() {
    $("#bannerImg").src = banners[bi][0]; $("#bannerTitle").textContent = banners[bi][1]; $("#bannerText").textContent = banners[bi][2];
    $("#bannerDots").innerHTML = banners.map((_, i) => `<button class="dot ${i === bi ? "active" : ""}" data-i="${i}"></button>`).join("");
    $$("#bannerDots .dot").forEach(d => d.onclick = () => { bi = +d.dataset.i; renderBanner() });
}
renderBanner(); setInterval(() => { bi = (bi + 1) % banners.length; renderBanner() }, 5000);

const testimonials = [
    ["Ramesh Thapa", "Homeowner · Battisputali", "“I bought an inverter and two tubular batteries from Swarnim Groups. The installation was same-day, the team was professional, and we haven't had a single power-cut interruption since. Truly honest people.”"],
    ["Sunita Sharma", "Restaurant Owner · New Road", "“Got my split AC installed in the peak of summer — same-day booking, neat mounting, zero mess. The price was the best I found in the whole market. Highly recommended for ACs!”"],
    ["Anil Gurung", "Solar Customer · Jhamsikhel", "“Swarnim Groups designed our full rooftop solar setup — 12 panels, hybrid inverter and batteries. My electricity bill dropped by nearly 40%. Their after-sales follow-up is something you rarely see.”"]
];
let ti = 0;
setInterval(() => {
    ti = (ti + 1) % testimonials.length;
    $("#quote").style.opacity = 0; $("#author").style.opacity = 0;
    setTimeout(() => { $("#quote").textContent = testimonials[ti][2]; $("#author").textContent = testimonials[ti][0] + " · " + testimonials[ti][1]; $("#quote").style.opacity = 1; $("#author").style.opacity = 1 }, 250);
}, 5500);

$$(".faq-q").forEach(btn => btn.onclick = () => btn.parentElement.classList.toggle("open"));

const modal = $("#galleryModal"), modalImg = $("#modalImg");
$$(".gallery-item img").forEach(img => img.onclick = () => { modalImg.src = img.src; modal.classList.add("open") });
$("#modalClose").onclick = () => modal.classList.remove("open");
modal.onclick = e => { if (e.target === modal) modal.classList.remove("open") };

const toast = $("#toast");
let csrf = "";

function showToast(message, error = false) {
    toast.textContent = message;
    toast.style.background = error ? "#b42318" : "var(--text)";
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 4500);
}

async function loadAuthState() {
    try {
        const response = await fetch("auth-status.php", { credentials: "same-origin", cache: "no-store" });
        const result = await response.json();
        csrf = result.data?.csrf || "";
        const user = result.data?.user;
        const authNav = $("#authNav");
        const mobileAuth = $("#mobileAuth");

        if (!user) {
            if (authNav) authNav.innerHTML = '<a class="cta" href="login.php">Login</a>';
            if (mobileAuth) mobileAuth.innerHTML = '<a href="login.php">Login</a>';
            return;
        }

        const safeName = String(user.name || "User").replace(/[<>&"']/g, "");
        const initial = safeName.trim().charAt(0).toUpperCase() || "U";
        if (authNav) {
            authNav.innerHTML = `<div class="user-menu"><button class="user-pill" id="userPill" type="button"><span class="user-avatar">${initial}</span><span>Hi, ${safeName}</span><span>⌄</span></button><div class="user-dropdown" id="userDropdown"><a href="profile.php">My Profile</a><a href="my-orders.php">My Orders</a><a href="index.php#contact">Contact Us</a>${user.role === "admin" ? '<a href="orders.php">All Orders</a><a href="contact-messages.php">Messages</a>' : ''}<a href="logout.php">Logout</a></div></div>`;
            const pill = $("#userPill"), dropdown = $("#userDropdown");
            pill.onclick = () => dropdown.classList.toggle("open");
            document.addEventListener("click", e => { if (!pill.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.remove("open"); });
        }
        if (mobileAuth) mobileAuth.innerHTML = `<a href="profile.php">My Profile</a><a href="my-orders.php">My Orders</a><a href="index.php#contact">Hi, ${safeName}</a><a href="logout.php">Logout</a>${user.role === "admin" ? '<a href="orders.php">All Orders</a><a href="contact-messages.php">Messages</a>' : ''}`;

        const name = $("#contactName"), email = $("#contactEmail");
        if (name && !name.value) name.value = user.name || "";
        if (email && !email.value) email.value = user.email || "";
    } catch (error) {
        console.warn("Auth status unavailable", error);
    }
}

loadAuthState();

const contactForm = $("#contactForm");
if (contactForm) {
    contactForm.addEventListener("submit", async e => {
        e.preventDefault();
        const button = contactForm.querySelector("button[type=submit]");
        button.disabled = true;
        button.textContent = "Sending…";
        try {
            if (!csrf) {
                const authResponse = await fetch("auth-status.php", { credentials: "same-origin", cache: "no-store" });
                const authResult = await authResponse.json();
                csrf = authResult.data?.csrf || "";
            }
            const response = await fetch("contact-api.php", {
                method: "POST",
                credentials: "same-origin",
                headers: { "X-CSRF-Token": csrf },
                body: new FormData(contactForm)
            });
            const result = await response.json();
            if (!result.ok) {
                showToast(result.message || "Could not send your message.", true);
                button.disabled = false;
                button.textContent = "Send Message ↗";
                return;
            }
            contactForm.reset();
            await loadAuthState();
            showToast(result.message || "Message sent successfully.");
            button.disabled = false;
            button.textContent = "Send Message ↗";
        } catch (error) {
            showToast("Server connection failed. Please try again.", true);
            button.disabled = false;
            button.textContent = "Send Message ↗";
        }
    });
}

document.addEventListener("mousemove", e => {
    document.documentElement.style.setProperty("--mx", (e.clientX / window.innerWidth * 100) + "%");
    document.documentElement.style.setProperty("--my", (e.clientY / window.innerHeight * 100) + "%");
});
