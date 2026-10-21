/* Solar load assessment: five steps shown as tabs. Settings come from the JSON block on assessment.php. */
(function () {
  "use strict";

  /* ---- Business settings ----
     Contact numbers and package names/prices come from the admin dashboard.
     The sizing limits below stay in code because they drive the calculator. */
  var settings = JSON.parse(document.getElementById("laSettings").textContent);
  var CONFIG = {
    whatsapp: settings.whatsapp,
    phoneDisplay: settings.phoneDisplay,
    sunHours: 4.5,          // average peak sun hours per day in Ghana
    systemLoss: 0.75,       // panel-to-socket efficiency
    panelWatts: 620,
    batteryUsable: 0.81,    // depth of discharge x inverter efficiency
    inverterSizes: [1.5, 3, 4.2, 6, 8, 10, 12, 16, 20],
    // share of daily energy the battery must carry, per usage mode
    batteryShare: { backup: 0.35, hybrid: 0.5, offgrid: 1 },
    packages: settings.packages
  };
  var leadSaved = false;

  function saveAssessment(c, property, appliances) {
    if (leadSaved) return;
    var payload = {
      csrf_token: settings.csrfToken,
      website: "",
      name: $("fName").value.trim(), phone: $("fPhone").value.trim(),
      location: $("fLocation").value.trim(), email: $("fEmail").value.trim(),
      notes: $("fNotes").value.trim(), property_type: property,
      usage_mode: c.mode, running_load_w: c.running,
      daily_energy_kwh: Number((c.daily / 1000).toFixed(2)),
      inverter_kva: c.kva, battery_kwh: c.battery, panel_count: c.panels,
      recommended_package: c.package ? c.package.name : "Custom Solar Package",
      appliances: appliances
    };
    fetch("submit-assessment.php", {
      method: "POST", headers: {"Content-Type": "application/json", "Accept": "application/json"},
      body: JSON.stringify(payload), credentials: "same-origin"
    }).then(function (response) {
      if (!response.ok) throw new Error("Save failed");
      leadSaved = true;
    }).catch(function () {
      /* WhatsApp remains available if the database is temporarily unavailable. */
    });
  }

  /* name, watts, default hours per day, duty cycle (share of time actually drawing power) */
  var APPLIANCES = [
    ["LED bulb", 10, 6, 1],
    ["Ceiling fan", 75, 8, 1],
    ["Standing fan", 60, 6, 1],
    ["Television", 100, 5, 1],
    ["Decoder / Wi-Fi router", 30, 10, 1],
    ["Phone charger", 10, 3, 1],
    ["Laptop", 65, 6, 1],
    ["Desktop computer", 200, 6, 1],
    ["Printer", 50, 1, 1],
    ["Fridge", 150, 24, 0.4],
    ["Deep freezer", 200, 24, 0.4],
    ["Air conditioner (1 HP)", 750, 6, 0.6],
    ["Air conditioner (1.5 HP)", 1100, 6, 0.6],
    ["Washing machine", 500, 1, 1],
    ["Microwave", 1000, 0.5, 1],
    ["Electric kettle", 1500, 0.25, 1],
    ["Electric iron", 1000, 0.5, 1],
    ["Blender", 400, 0.25, 1],
    ["Water pump (1 HP)", 750, 1, 1]
  ].map(function (a) { return { name: a[0], watts: a[1], hours: a[2], duty: a[3], qty: 0 }; });

  var $ = function (id) { return document.getElementById(id); };
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined) n.textContent = text;
    return n;
  }
  function icon(id) {
    var s = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    s.setAttribute("class", "ico");
    var u = document.createElementNS("http://www.w3.org/2000/svg", "use");
    u.setAttribute("href", "#" + id);
    s.appendChild(u);
    return s;
  }
  function waLink(text) { return "https://wa.me/" + CONFIG.whatsapp + "?text=" + encodeURIComponent(text); }
  function num(n) { return Math.round(n).toLocaleString("en-GB"); }

  /* ---- Load assessment ---- */
  var dlg = $("la"), step = 0, LAST = 4;
  var steps = dlg.querySelectorAll(".la-step");
  var tabs = $("laTabs").children;
  var errorBox = $("laError"), live = $("laLive");
  var backBtn = $("laBack"), nextBtn = $("laNext");

  function selected() { return APPLIANCES.filter(function (a) { return a.qty > 0; }); }

  function calc() {
    var mode = $("mode").value;
    var running = 0, daily = 0;
    selected().forEach(function (a) {
      running += a.qty * a.watts;
      daily += a.qty * a.watts * a.hours * a.duty;
    });
    var needKva = running * 1.25 / 0.8 / 1000;
    var kva = null;
    for (var i = 0; i < CONFIG.inverterSizes.length; i++) {
      if (CONFIG.inverterSizes[i] >= needKva) { kva = CONFIG.inverterSizes[i]; break; }
    }
    var batteryNeed = daily / 1000 * CONFIG.batteryShare[mode] / CONFIG.batteryUsable;
    var battery = Math.max(2.4, Math.ceil(batteryNeed * 10) / 10);
    var panels = Math.max(2, Math.ceil(daily / (CONFIG.sunHours * CONFIG.systemLoss) / CONFIG.panelWatts));
    var matchedPackage = null;
    for (var p = 0; p < CONFIG.packages.length; p++) {
      var candidate = CONFIG.packages[p];
      if (kva !== null && kva <= candidate.maxKva && battery <= candidate.maxBatteryKwh && panels <= candidate.maxPanels) {
        matchedPackage = candidate;
        break;
      }
    }
    return {
      mode: mode, running: running, daily: daily, kva: kva, battery: battery, panels: panels,
      arrayKw: panels * CONFIG.panelWatts / 1000,
      package: matchedPackage
    };
  }

  function updateLive() {
    if (step === 1 || step === 2) {
      var c = calc();
      live.textContent = "";
      live.append("Running load: ", el("b", "", num(c.running) + " W"), "  |  Daily use: ", el("b", "", (c.daily / 1000).toFixed(1) + " kWh"));
    } else {
      live.textContent = "";
    }
  }

  function renderAppliances() {
    var list = $("applList");
    list.textContent = "";
    APPLIANCES.forEach(function (a) {
      var row = el("div", "appl" + (a.qty > 0 ? " has" : ""));
      var info = el("div");
      info.append(el("div", "name", a.name), el("div", "watts", num(a.watts) + " W each"));
      var st = el("div", "stepper");
      var minus = el("button", "", "−"), out = el("output", "", String(a.qty)), plus = el("button", "", "+");
      minus.type = plus.type = "button";
      minus.setAttribute("aria-label", "Fewer: " + a.name);
      plus.setAttribute("aria-label", "More: " + a.name);
      function set(q) {
        a.qty = Math.min(99, Math.max(0, q));
        out.textContent = a.qty;
        row.classList.toggle("has", a.qty > 0);
        errorBox.textContent = "";
        updateLive();
      }
      minus.addEventListener("click", function () { set(a.qty - 1); });
      plus.addEventListener("click", function () { set(a.qty + 1); });
      st.append(minus, out, plus);
      row.append(info, st);
      list.appendChild(row);
    });
  }

  function renderHours() {
    var body = $("hoursBody");
    body.textContent = "";
    selected().forEach(function (a) {
      var tr = el("tr");
      var input = el("input");
      input.type = "number"; input.min = "0"; input.max = "24"; input.step = "0.25";
      input.value = a.hours;
      input.setAttribute("aria-label", "Hours per day: " + a.name);
      input.addEventListener("input", function () {
        var v = parseFloat(input.value);
        a.hours = isNaN(v) ? 0 : Math.min(24, Math.max(0, v));
        updateLive();
      });
      var td = el("td"); td.appendChild(input);
      tr.append(el("td", "", a.name), el("td", "", String(a.qty)), td);
      body.appendChild(tr);
    });
  }

  var MODE_LABEL = { backup: "Backup when the power goes off", hybrid: "Solar by day, battery through the evening", offgrid: "Fully off-grid, day and night" };

  function renderResult() {
    var c = calc(), box = $("resultBox"), matched = c.package;
    var property = (dlg.querySelector("input[name=property]:checked") || {}).value || "";
    var name = $("fName").value.trim(), phone = $("fPhone").value.trim();
    var locationText = $("fLocation").value.trim(), email = $("fEmail").value.trim(), notes = $("fNotes").value.trim();
    var oversize = c.kva === null;
    var pkgName = matched ? matched.name : "Custom Solar Package";
    var kvaText = oversize ? "Above 20 kVA" : c.kva + " kVA";

    box.textContent = "";
    box.appendChild(el("h3", "", name ? name.split(" ")[0] + ", here is your recommendation" : "Your recommendation"));

    var pkg = el("div", "result-pkg");
    pkg.style.marginTop = "14px";
    pkg.append(el("span", "tag", "RECOMMENDED FOR YOU"), el("h3", "", pkgName));
    if (matched) {
      pkg.append(el("div", "price", matched.price), el("p", "", "Your estimated load fits this advertised package. Final sizing is confirmed after a free site assessment."));
    } else if (oversize) {
      pkg.append(el("div", "price", "Price sent after your message"), el("p", "", "Your load is larger than a standard package, so there is no fixed price to show here. Send us this assessment on WhatsApp and we will reply with your price once we receive your message."));
    } else {
      pkg.append(el("div", "price", "Price sent after your message"), el("p", "", "Your load needs a custom system beyond the advertised packages. Send us this assessment on WhatsApp and we will reply with your price once we receive your message."));
    }
    box.appendChild(pkg);

    var metrics = el("div", "metrics");
    [["Running load", num(c.running) + " W"], ["Daily energy", (c.daily / 1000).toFixed(1) + " kWh"],
     ["Inverter", kvaText], ["Battery", c.battery + " kWh"],
     ["Solar panels", c.panels + " x " + CONFIG.panelWatts + " W"], ["Array size", c.arrayKw.toFixed(1) + " kWp"]]
      .forEach(function (m) { var d = el("div", "metric"); d.append(el("span", "", m[0]), el("b", "", m[1])); metrics.appendChild(d); });
    box.appendChild(metrics);

    var ul = el("ul", "result-list");
    selected().forEach(function (a) { ul.appendChild(el("li", "", a.qty + " x " + a.name + ", " + a.hours + " h/day")); });
    box.appendChild(ul);
    box.appendChild(el("p", "result-note", "This is an estimate based on typical appliance wattages. Final sizing and price are confirmed after a free site assessment."));

    var msg = [
      "Hello KYNOS Solar Systems, I completed the Solar Load Assessment on your website.",
      "",
      "Name: " + name,
      "Telephone: " + phone,
      "Location: " + locationText
    ];
    if (email) msg.push("Email: " + email);
    msg.push("Property: " + property, "Usage: " + MODE_LABEL[c.mode], "", "Appliances:");
    selected().forEach(function (a) { msg.push("- " + a.qty + " x " + a.name + " (" + a.watts + " W), " + a.hours + " h/day"); });
    msg.push("", "Running load: " + num(c.running) + " W", "Daily energy: " + (c.daily / 1000).toFixed(1) + " kWh",
      "Suggested system: " + kvaText + " inverter, " + c.battery + " kWh battery, " + c.panels + " x " + CONFIG.panelWatts + " W panels",
      "Suggested package: " + pkgName + (matched ? " (" + matched.price + ")" : ""));
    if (notes) msg.push("", "Notes: " + notes);
    if (!matched) msg.push("", "Please send me the price for this system.");

    saveAssessment(c, property, selected().map(function (a) {
      return { name: a.name, quantity: a.qty, watts: a.watts, hours_per_day: a.hours };
    }));

    if (!matched) box.appendChild(el("p", "result-next", "Next step: tap the green button to send this to KYNOS. Your price comes back to you on WhatsApp after we receive it."));
    var actions = el("div", "result-actions");
    var send = el("a", "btn btn-wa");
    send.href = waLink(msg.join("\n")); send.target = "_blank"; send.rel = "noopener";
    var wa = icon("i-wa"); wa.classList.add("wa");
    send.append(wa, "Send to KYNOS on WhatsApp");
    var call = el("a", "btn btn-outline");
    call.href = "tel:+" + CONFIG.whatsapp;
    call.append(icon("i-phone"), "Call " + CONFIG.phoneDisplay);
    actions.append(send, call);
    box.appendChild(actions);
  }

  function validate(step) {
    if (step === 0 && !dlg.querySelector("input[name=property]:checked")) return "Please choose a property type.";
    if (step === 1 && !selected().length) return "Add at least one appliance to continue.";
    if (step === 2 && calc().daily <= 0) return "Enter the hours of use for at least one appliance.";
    if (step === 3) {
      if ($("fName").value.trim().length < 2) return "Please enter your full name.";
      if (!/^(\+?233|0)\d{9}$/.test($("fPhone").value.replace(/[\s-]/g, ""))) return "Please enter a valid Ghana phone number, e.g. 053 040 9118.";
      if ($("fLocation").value.trim().length < 2) return "Please enter your location.";
      var email = $("fEmail").value.trim();
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return "That email address does not look right.";
    }
    return "";
  }

  function show(n, focusTab) {
    step = n;
    steps.forEach(function (s, i) { s.classList.toggle("on", i === n); });
    for (var i = 0; i < tabs.length; i++) {
      tabs[i].classList.toggle("done", i < n);
      tabs[i].setAttribute("aria-selected", i === n ? "true" : "false");
      tabs[i].tabIndex = i === n ? 0 : -1;
    }
    if (focusTab) tabs[n].focus();
    if (n === 2) renderHours();
    if (n === LAST) renderResult();
    backBtn.style.visibility = n === 0 ? "hidden" : "visible";
    nextBtn.textContent = "";
    if (n === LAST) nextBtn.append("Start again");
    else nextBtn.append(n === 3 ? "See my recommendation " : "Next ", icon("i-arrow"));
    errorBox.textContent = "";
    updateLive();
  }

  /* after a step change, bring the top of the panel back into view if the user had scrolled past it */
  function toTop() {
    if (dlg.getBoundingClientRect().top < 0) dlg.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  /* move to step n, stopping at the first earlier step that is not filled in yet */
  function go(n, focusTab) {
    if (n === step) return;
    for (var s = 0; s < n; s++) {
      var err = validate(s);
      if (err) {
        if (s !== step) show(s, focusTab);
        errorBox.textContent = err;
        toTop();
        return;
      }
    }
    show(n, focusTab);
    toTop();
  }

  function reset() {
    leadSaved = false;
    APPLIANCES = APPLIANCES.filter(function (a) { return !a.custom; });
    APPLIANCES.forEach(function (a) { a.qty = 0; });
    $("laForm").reset();
    renderAppliances();
    show(0);
  }

  nextBtn.addEventListener("click", function () {
    if (step === LAST) { reset(); toTop(); return; }
    go(step + 1);
  });
  backBtn.addEventListener("click", function () { if (step > 0) go(step - 1); });
  for (var t = 0; t < tabs.length; t++) {
    (function (n) { tabs[n].addEventListener("click", function () { go(n); }); })(t);
  }
  $("laTabs").addEventListener("keydown", function (e) {
    var d = e.key === "ArrowRight" ? 1 : e.key === "ArrowLeft" ? -1 : 0;
    if (e.key === "Home") d = -step;
    if (e.key === "End") d = LAST - step;
    if (!d) return;
    e.preventDefault();
    go(Math.min(LAST, Math.max(0, step + d)), true);
  });
  $("laForm").addEventListener("submit", function (e) { e.preventDefault(); nextBtn.click(); });
  $("laForm").addEventListener("change", function () { errorBox.textContent = ""; updateLive(); });

  $("cAdd").addEventListener("click", function () {
    var name = $("cName").value.trim(), watts = parseInt($("cWatts").value, 10);
    if (!name || !(watts > 0) || watts > 20000) { errorBox.textContent = "Enter an appliance name and its wattage (1 to 20,000 W)."; return; }
    APPLIANCES.push({ name: name, watts: watts, hours: 4, duty: 1, qty: 1, custom: true });
    $("cName").value = ""; $("cWatts").value = "";
    renderAppliances();
    errorBox.textContent = "";
    updateLive();
    $("applList").lastElementChild.scrollIntoView({ block: "nearest" });
  });

  renderAppliances();
  show(0);
})();
