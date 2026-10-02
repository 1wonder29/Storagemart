    
  const lines = [
    "██████╗ ██╗   ██╗██╗██╗     ████████╗",
    "██╔══██╗██║   ██║██║██║     ╚══██╔══╝",
    "██████╔╝██║   ██║██║██║        ██║",
    "██╔══██╗██║   ██║██║██║        ██║",
    "██████╔╝╚██████╔╝██║███████╗   ██║",
    "╚═════╝  ╚═════╝ ╚═╝╚══════╝   ╚═╝",
    "",
    "Built by Ricafort, Roland Josh M.",
    "GitHub: https://github.com/Ouaaaa"
  ];

  const colors = ["#0f0", "#f00", "#0ff", "#ff0", "#f0f"];
  const baseStyle = "font-family:monospace;font-size:15px;font-weight:bold;";

  let tick = 0;

  setInterval(() => {
    console.clear();

    const color = colors[tick % colors.length];

    lines.forEach((line, index) => {
      const isFooter = index >= lines.length - 2;

      console.log(
        "%c" + line,
        `
          color: ${isFooter ? "#9a9a9a" : color};
          ${baseStyle}
          ${!isFooter ? "text-shadow:0 0 8px " + color + ";" : ""}
        `
      );
    });

    tick++;
  }, 140); // speed (ms)
