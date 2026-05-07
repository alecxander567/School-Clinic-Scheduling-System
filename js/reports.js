document.addEventListener("DOMContentLoaded", function () {
  /* ── Chart defaults ── */
  Chart.defaults.font.family = "'DM Sans', sans-serif";
  Chart.defaults.font.size = 11;
  Chart.defaults.color = "#6b8fa7";

  // ── Theme palette (mirrors CSS variables) ──────────────────────────────
  const COLORS = {
    primary: "#1e5ba8",
    primaryDark: "#004b87",
    primaryDeep: "#0f2044",
    blue: "#2563eb",
    indigo: "#3730a3",
    amber: "#f59e0b",
    purple: "#7c3aed",
    rose: "#e11d48",

    tintBlue: "#e8f1f8",
    tintIndigo: "#e6eeff",
    tintAmber: "#faeeda",
    tintPurple: "#f0eeff",
    tintRose: "#fcebeb",

    borderBlue: "#bed6e8",
    borderIndigo: "#c7d2fe",
    borderAmber: "#fde68a",
    borderPurple: "#ddd6fe",
    borderRose: "#fecaca",

    textMuted: "#6b8fa7",
    surface: "#ffffff",

    // Multi-segment palette: tint fills + matching border colors
    palette: [
      "#e8f1f8",
      "#e6eeff",
      "#faeeda",
      "#f0eeff",
      "#fcebeb",
      "#d4e6f1",
      "#c7d2fe",
    ],
    paletteBorder: [
      "#1e5ba8",
      "#3730a3",
      "#f59e0b",
      "#7c3aed",
      "#e11d48",
      "#2563eb",
      "#6d28d9",
    ],
  };

  // Shared tooltip style
  const tooltipDefaults = {
    backgroundColor: COLORS.surface,
    titleColor: COLORS.primaryDeep,
    bodyColor: COLORS.textMuted,
    borderColor: COLORS.borderBlue,
    borderWidth: 1,
    padding: 10,
    cornerRadius: 8,
  };

  /* ── Empty state helper ────────────────────────────────────────────────── */
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
           viewBox="0 0 24 24" style="color:#bed6e8; margin-bottom: 12px;">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0
             0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0
             0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
      </svg>
      <p style="font-size:13px; font-weight:600; color:#6b8fa7; margin: 0 0 4px 0;">
        ${message || "No data available"}
      </p>
      <p style="font-size:11px; color:#8fa6ba; margin: 0;">
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
              backgroundColor: COLORS.palette.slice(0, labels.length),
              borderColor: COLORS.paletteBorder.slice(0, labels.length),
              borderWidth: 2,
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
                color: COLORS.textMuted,
              },
            },
            tooltip: {
              ...tooltipDefaults,
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
      const maxIdx = counts.indexOf(Math.max(...counts));
      new Chart(hourlyCanvas.getContext("2d"), {
        type: "bar",
        data: {
          labels: hours.map((h) => {
            const d = new Date(2000, 0, 1, h);
            return d.toLocaleTimeString("en-US", {
              hour: "numeric",
              hour12: true,
            });
          }),
          datasets: [
            {
              label: "Appointments",
              data: counts,
              backgroundColor: counts.map((_, i) =>
                i === maxIdx ? COLORS.primary : COLORS.tintBlue,
              ),
              borderColor: counts.map((_, i) =>
                i === maxIdx ? COLORS.primaryDark : COLORS.borderBlue,
              ),
              borderWidth: 2,
              borderRadius: 6,
              borderSkipped: false,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              grid: { display: false },
              border: { color: COLORS.borderBlue },
              ticks: { color: COLORS.textMuted },
            },
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1, color: COLORS.textMuted },
              grid: { color: COLORS.tintBlue },
              border: { dash: [4, 4], color: "transparent" },
            },
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              ...tooltipDefaults,
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
          labels: dates.map((d) => {
            const dt = new Date(d + "T00:00:00");
            return dt.toLocaleDateString("en-US", {
              month: "short",
              day: "numeric",
            });
          }),
          datasets: [
            {
              label: "Appointments",
              data: appointments,
              borderColor: COLORS.primary,
              backgroundColor: COLORS.tintBlue,
              tension: 0.4,
              fill: true,
              pointRadius: 3,
              pointHoverRadius: 6,
              pointBackgroundColor: COLORS.primary,
              pointBorderColor: COLORS.surface,
              pointBorderWidth: 2,
              borderWidth: 2.5,
            },
            {
              label: "Visits",
              data: visits,
              borderColor: COLORS.indigo,
              backgroundColor: COLORS.tintIndigo,
              tension: 0.4,
              fill: true,
              pointRadius: 3,
              pointHoverRadius: 6,
              pointBackgroundColor: COLORS.indigo,
              pointBorderColor: COLORS.surface,
              pointBorderWidth: 2,
              borderWidth: 2.5,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: "index", intersect: false },
          scales: {
            x: {
              grid: { display: false },
              border: { color: COLORS.borderBlue },
              ticks: { color: COLORS.textMuted, maxTicksLimit: 10 },
            },
            y: {
              beginAtZero: true,
              grid: { color: COLORS.tintBlue },
              border: { dash: [4, 4], color: "transparent" },
              ticks: { stepSize: 1, color: COLORS.textMuted },
            },
          },
          plugins: {
            legend: {
              position: "bottom",
              labels: {
                usePointStyle: true,
                pointStyleWidth: 8,
                padding: 16,
                color: COLORS.textMuted,
              },
            },
            tooltip: tooltipDefaults,
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
              borderColor: COLORS.paletteBorder.slice(0, names.length),
              borderWidth: 2,
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
              grid: { color: COLORS.tintBlue },
              border: { dash: [4, 4], color: "transparent" },
              ticks: { stepSize: 1, color: COLORS.textMuted },
            },
            y: {
              grid: { display: false },
              border: { color: COLORS.borderBlue },
              ticks: { color: COLORS.textMuted },
            },
          },
          plugins: {
            legend: { display: false },
            tooltip: tooltipDefaults,
          },
        },
      });
    }
  }
});
