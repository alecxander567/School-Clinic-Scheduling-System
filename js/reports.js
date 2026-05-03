document.addEventListener("DOMContentLoaded", function () {
  /* ── Chart defaults ── */
  Chart.defaults.font.family = "'Sora', sans-serif";
  Chart.defaults.font.size = 11;
  Chart.defaults.color = "#9ca3af";

  const COLORS = {
    green: "#2d8a6e",
    teal: "#3b9e7e",
    mint: "#4aae8e",
    sage: "#59be9e",
    light: "#68ceae",
    blue: "#3b82f6",
    amber: "#f59e0b",
    purple: "#8b5cf6",
    rose: "#f43f5e",
    palette: [
      "#2d8a6e",
      "#3b9e7e",
      "#4aae8e",
      "#59be9e",
      "#68ceae",
      "#86dfc4",
      "#a4f0da",
    ],
  };

  /* ── Empty state helper ────────────────────────────────────
       Hides the canvas and injects a friendly placeholder in
       the same .chart-container wrapper.
    ── */
  function isEmpty(data) {
    if (!data || data.length === 0) return true;
    if (data.every((v) => typeof v === "number"))
      return data.every((v) => v === 0);
    return false;
  }

  function showEmptyState(canvas, message) {
    canvas.style.display = "none";

    const placeholder = document.createElement("div");
    placeholder.className = "chart-empty";
    placeholder.innerHTML = `
            <svg width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.5"
                 viewBox="0 0 24 24" style="color:#d1d5db; margin-bottom: 12px;">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002
                       2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6
                       0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0
                       012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p style="font-size:13px; font-weight:600; color:#6b7280; margin: 0 0 4px 0;">
                ${message || "No data available"}
            </p>
            <p style="font-size:11px; color:#9ca3af; margin: 0;">
                Try selecting a different date or period
            </p>
        `;

    canvas.parentElement.appendChild(placeholder);
  }

  /* ── Service Breakdown Doughnut ── */
  const serviceCanvas = document.getElementById("serviceChart");
  if (serviceCanvas) {
    const labels = JSON.parse(serviceCanvas.dataset.labels || "[]");
    const values = JSON.parse(serviceCanvas.dataset.values || "[]");

    if (isEmpty(values)) {
      showEmptyState(serviceCanvas, "No service data for this date");
    } else {
      new Chart(serviceCanvas.getContext("2d"), {
        type: "doughnut",
        data: {
          labels,
          datasets: [
            {
              data: values,
              backgroundColor: COLORS.palette,
              borderWidth: 0,
              hoverOffset: 6,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "68%",
          plugins: {
            legend: {
              position: "bottom",
              labels: {
                padding: 14,
                usePointStyle: true,
                pointStyleWidth: 8,
                font: { size: 11 },
              },
            },
            tooltip: {
              callbacks: {
                label: (ctx) => ` ${ctx.label}: ${ctx.parsed} appointments`,
              },
            },
          },
        },
      });
    }
  }

  /* ── Hourly Distribution Bar ── */
  const hourlyCanvas = document.getElementById("hourlyChart");
  if (hourlyCanvas) {
    const hours = JSON.parse(hourlyCanvas.dataset.hours || "[]");
    const counts = JSON.parse(hourlyCanvas.dataset.counts || "[]");

    if (isEmpty(counts)) {
      showEmptyState(hourlyCanvas, "No appointment activity this day");
    } else {
      new Chart(hourlyCanvas.getContext("2d"), {
        type: "bar",
        data: {
          labels: hours.map((h) => h + ":00"),
          datasets: [
            {
              label: "Appointments",
              data: counts,
              backgroundColor: counts.map((_, i) =>
                i === counts.indexOf(Math.max(...counts)) ?
                  COLORS.green
                : "rgba(45,138,110,0.25)",
              ),
              borderRadius: 6,
              borderSkipped: false,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: { grid: { display: false }, border: { display: false } },
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1 },
              grid: { color: "#f3f4f6" },
              border: { display: false },
            },
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: (ctx) =>
                  ` ${ctx.parsed.y} appointment${ctx.parsed.y !== 1 ? "s" : ""}`,
              },
            },
          },
        },
      });
    }
  }

  /* ── Monthly Daily Trend Line ── */
  const trendCanvas = document.getElementById("dailyTrendChart");
  if (trendCanvas) {
    const dates = JSON.parse(trendCanvas.dataset.dates || "[]");
    const appointments = JSON.parse(trendCanvas.dataset.appointments || "[]");
    const visits = JSON.parse(trendCanvas.dataset.visits || "[]");

    if (isEmpty(dates)) {
      showEmptyState(trendCanvas, "No data for this month");
    } else {
      new Chart(trendCanvas.getContext("2d"), {
        type: "line",
        data: {
          labels: dates.map((d) => d.substring(8)),
          datasets: [
            {
              label: "Appointments",
              data: appointments,
              borderColor: COLORS.green,
              backgroundColor: "rgba(45,138,110,0.08)",
              tension: 0.4,
              fill: true,
              pointRadius: 3,
              pointHoverRadius: 6,
              pointBackgroundColor: COLORS.green,
              borderWidth: 2,
            },
            {
              label: "Visits",
              data: visits,
              borderColor: COLORS.blue,
              backgroundColor: "rgba(59,130,246,0.06)",
              tension: 0.4,
              fill: true,
              pointRadius: 3,
              pointHoverRadius: 6,
              pointBackgroundColor: COLORS.blue,
              borderWidth: 2,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: "index", intersect: false },
          scales: {
            x: { grid: { display: false }, border: { display: false } },
            y: {
              beginAtZero: true,
              grid: { color: "#f3f4f6" },
              border: { display: false },
              ticks: { stepSize: 1 },
            },
          },
          plugins: {
            legend: {
              position: "top",
              labels: { usePointStyle: true, pointStyleWidth: 8, padding: 16 },
            },
          },
        },
      });
    }
  }

  /* ── Monthly Service Popularity Horizontal Bar ── */
  const monthlyServiceCanvas = document.getElementById("monthlyServiceChart");
  if (monthlyServiceCanvas) {
    const names = JSON.parse(monthlyServiceCanvas.dataset.names || "[]");
    const counts = JSON.parse(monthlyServiceCanvas.dataset.counts || "[]");

    if (isEmpty(counts)) {
      showEmptyState(monthlyServiceCanvas, "No service data for this month");
    } else {
      new Chart(monthlyServiceCanvas.getContext("2d"), {
        type: "bar",
        data: {
          labels: names,
          datasets: [
            {
              label: "Appointments",
              data: counts,
              backgroundColor: COLORS.palette.slice(0, names.length),
              borderRadius: 5,
              borderSkipped: false,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: "y",
          scales: {
            x: {
              beginAtZero: true,
              grid: { color: "#f3f4f6" },
              border: { display: false },
              ticks: { stepSize: 1 },
            },
            y: { grid: { display: false }, border: { display: false } },
          },
          plugins: { legend: { display: false } },
        },
      });
    }
  }
});
