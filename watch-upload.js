import chokidar from 'chokidar';
import { exec } from 'child_process';
import path from 'path';

const host = process.env.DEPLOY_HOST || 'nailedit.ee';
const username = process.env.DEPLOY_USER || 'nailedit';
const port = Number(process.env.DEPLOY_PORT || 1022);

const localBase = process.env.DEPLOY_LOCAL || process.cwd();
const remoteBase = process.env.DEPLOY_REMOTE || '/home/nailedit/www/wp-content/themes/nailedit';

const ignored = [
  /(^|[\/\\])node_modules($|[\/\\])/,
  /(^|[\/\\])\.git($|[\/\\])/,
  /(^|[\/\\])\.idea($|[\/\\])/,
  /(^|[\/\\])\.vscode($|[\/\\])/,
  /\.DS_Store$/,
  /\.log$/,
  /deploy\.sh$/,
  /watch-deploy\.sh$/,
  /watch-upload\.js$/,
];

const debounceMs = Number(process.env.DEPLOY_DEBOUNCE || 300);
const timers = new Map();

const uploadFile = (filePath) => {
  const relative = path.relative(localBase, filePath);
  const remotePath = path.posix.join(remoteBase, relative.split(path.sep).join('/'));
  const remoteDir = path.posix.dirname(remotePath);

  console.log('⬆️  Upload:', relative);
  
  const cmd = `ssh -p ${port} ${username}@${host} "mkdir -p ${remoteDir}" && scp -P ${port} "${filePath}" ${username}@${host}:"${remotePath}"`;
  
  exec(cmd, (error, stdout, stderr) => {
    if (error) {
      console.error('✗ Upload failed:', relative, error.message);
    } else {
      console.log('✅ Uploaded:', relative);
    }
  });
};

const queueUpload = (filePath) => {
  const existing = timers.get(filePath);
  if (existing) {
    clearTimeout(existing);
  }

  const timer = setTimeout(() => {
    timers.delete(filePath);
    uploadFile(filePath);
  }, debounceMs);

  timers.set(filePath, timer);
};

const start = () => {
  console.log('🔌 SSH:', `${username}@${host}:${port}`);
  console.log('👀 Watching:', localBase);
  console.log('📡 Remote:', remoteBase);
  console.log('');

  chokidar
    .watch(localBase, {
      ignored,
      ignoreInitial: true,
    })
    .on('change', queueUpload)
    .on('add', queueUpload);
};

start();
