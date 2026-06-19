const port = parseInt(process.env.BROWSER_SYNC_PORT || '3000', 10);

module.exports = {
  port,
  ui: { port: port + 1 },
  files: [
    'app/**/*.php',
    'config/**/*.php',
    'public/assets/css/**/*.css',
    'public/assets/js/**/*.js',
  ],
  notify: false,
  open: false,
  ghostMode: false,
  reloadDelay: 150,
};
