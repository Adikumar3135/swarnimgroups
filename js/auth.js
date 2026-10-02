const $=s=>document.querySelector(s),$$=s=>document.querySelectorAll(s);
let csrf="";

document.addEventListener("DOMContentLoaded",async()=>{
  requestAnimationFrame(()=>document.body.classList.add("ready"));

  const transition=url=>{
    document.body.classList.remove("ready");
    document.body.classList.add("page-exit");
    setTimeout(()=>location.href=url,380);
  };

  $$("[data-page]").forEach(a=>{
    a.addEventListener("click",e=>{
      const href=a.getAttribute("href");
      if(!href||href.startsWith("#")||href.startsWith("http")||href.startsWith("tel:")||href.startsWith("mailto:"))return;
      e.preventDefault();transition(href);
    });
  });

  $$("[data-password]").forEach(btn=>{
    btn.addEventListener("click",()=>{
      const input=document.getElementById(btn.dataset.password);
      input.type=input.type==="password"?"text":"password";
      btn.textContent=input.type==="password"?"◉":"◌";
    });
  });

  try{
    const r=await fetch("auth-status.php",{credentials:"same-origin",cache:"no-store"});
    const j=await r.json();
    csrf=j.data?.csrf||"";
  }catch(e){}

  const message=(text,type="error")=>{
    const box=$("#message");if(!box)return;
    box.textContent=text;box.className="message show "+type;
  };

  const post=async(url,form)=>{
    const body=new FormData(form);
    const r=await fetch(url,{method:"POST",body,credentials:"same-origin",headers:{"X-CSRF-Token":csrf}});
    return await r.json();
  };

  const login=$("#loginForm");
  if(login){
    login.addEventListener("submit",async e=>{
      e.preventDefault();
      const btn=login.querySelector("button[type=submit]");btn.disabled=true;btn.textContent="Signing in…";
      try{
        const j=await post("login-api.php",login);
        if(!j.ok){message(j.message);btn.disabled=false;btn.textContent="Login to Swarnim →";return}
        message("Login successful. Welcome "+j.data.user.name+"!","success");
        setTimeout(()=>transition(j.data.redirect||"index.html"),650);
      }catch(err){message("Unable to connect to the server.");btn.disabled=false;btn.textContent="Login to Swarnim →"}
    });
  }

  const register=$("#registerForm");
  if(register){
    const pass=$("#regPassword"),confirm=$("#regConfirm"),meter=$("#meter");
    pass?.addEventListener("input",()=>{
      const v=pass.value;let score=0;
      if(v.length>=8)score++;if(/[A-Z]/.test(v))score++;if(/[0-9]/.test(v))score++;if(/[^A-Za-z0-9]/.test(v))score++;
      if(meter){meter.style.width=(score*25)+"%";meter.style.background=score<2?"#ff7272":score<4?"#f6c64d":"#5ee6a1"}
    });
    register.addEventListener("submit",async e=>{
      e.preventDefault();
      if(pass.value!==confirm.value){message("Passwords do not match.");return}
      const btn=register.querySelector("button[type=submit]");btn.disabled=true;btn.textContent="Creating account…";
      try{
        const j=await post("register-api.php",register);
        if(!j.ok){message(j.message);btn.disabled=false;btn.textContent="Create My Account →";return}
        message("Account created successfully. Welcome "+j.data.user.name+"!","success");
        setTimeout(()=>transition("index.php"),750);
      }catch(err){message("Unable to connect to the server.");btn.disabled=false;btn.textContent="Create My Account →"}
    });
  }

  const forgot=$("#forgotForm");
  if(forgot){
    forgot.addEventListener("submit",async e=>{
      e.preventDefault();
      const btn=forgot.querySelector("button[type=submit]");btn.disabled=true;btn.textContent="Sending email…";
      try{
        const j=await post("request-reset.php",forgot);
        if(!j.ok){message(j.message);btn.disabled=false;btn.textContent="Send Reset Link →";return}
        message("If the account exists, a reset link has been sent to the email address.","success");
        forgot.reset();
        btn.textContent="Email Sent ✓";
      }catch(err){message("Unable to connect to the server.");btn.disabled=false;btn.textContent="Send Reset Link →"}
    });
  }

  const reset=$("#resetForm");
  if(reset){
    reset.addEventListener("submit",async e=>{
      e.preventDefault();
      const p=$("#newPassword").value,c=$("#newConfirm").value;
      if(p!==c){message("Passwords do not match.");return}
      const token=new URLSearchParams(location.search).get("token");
      if(!token){message("Invalid reset link.");return}
      const form=new FormData(reset);form.append("token",token);
      const btn=reset.querySelector("button[type=submit]");btn.disabled=true;btn.textContent="Updating…";
      try{
        const r=await fetch("reset-password-api.php",{method:"POST",body:form,credentials:"same-origin",headers:{"X-CSRF-Token":csrf}});
        const j=await r.json();
        if(!j.ok){message(j.message);btn.disabled=false;btn.textContent="Update Password →";return}
        message("Password updated successfully. Redirecting to login…","success");
        setTimeout(()=>transition("login.php"),900);
      }catch(err){message("Unable to connect to the server.");btn.disabled=false;btn.textContent="Update Password →"}
    });
  }
});