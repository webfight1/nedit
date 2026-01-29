# NailedIt WordPress Theme

## Development

### Setup
```bash
npm install
```

### Development Mode
Run Tailwind CSS and Sass watchers:
```bash
npm run dev
```

### Build for Production
Compile and minify CSS:
```bash
npm run build
```

## Deployment to VPS

### Prerequisites
- SSH access to the VPS server (root@45.93.139.96)
- SSH key authentication configured (recommended)

### Deploy Commands

**Deploy current files:**
```bash
npm run deploy
```

**Build and deploy:**
```bash
npm run deploy:build
```

### Manual Deployment
You can also run the deployment script directly:
```bash
./deploy.sh
```

### What Gets Deployed
The deployment script syncs all theme files to `/var/www/html/nailedit/wp-content/themes/nailedit` on the VPS, excluding:
- `node_modules/`
- `.git/`
- `.DS_Store`
- `deploy.sh`
- `.gitignore`
- `*.log`

### SSH Key Setup (Recommended)
To avoid entering password on each deployment:

1. Generate SSH key (if you don't have one):
```bash
ssh-keygen -t rsa -b 4096
```

2. Copy key to VPS:
```bash
ssh-copy-id root@45.93.139.96
```

3. Test connection:
```bash
ssh root@45.93.139.96
```

Now `npm run deploy` will work without password prompts.
