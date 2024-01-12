# -*- mode: python ; coding: utf-8 -*-


block_cipher = None


a = Analysis(['kill_process.py'],
             pathex=['C:\\dev\\works\\padbol\\poc\\desktop-app\\app_kill_process'],
             binaries=[],
             datas=[],
             hiddenimports=[],
             hookspath=[],
             runtime_hooks=[],
             excludes=[],
             win_no_prefer_redirects=False,
             win_private_assemblies=False,
             cipher=block_cipher,
             noarchive=False)
pyz = PYZ(a.pure, a.zipped_data,
             cipher=block_cipher)
exe = EXE(pyz,
          a.scripts,
          a.binaries,
          a.zipfiles,
          a.datas,
          name='kill_process',
          debug=False,
          strip=False,
          upx=True,
          runtime_tmpdir=None,
          console=True)
coll = COLLECT(exe,
               a.binaries,
               a.zipfiles,
               a.datas,
               strip=False,
               upx=True,
               upx_exclude=[],
               name='kill_process')
