import os
import subprocess
import sys

# Lista de módulos requeridos
required_modules = ["mysql-connector-python", "requests"]

# Función para instalar módulos automáticamente
def install_modules():
    for module in required_modules:
        try:
            __import__(module.replace('-', '_'))  # Intentar importar el módulo
        except ImportError:
            print(f"Instalando el módulo {module}...")
            subprocess.check_call([sys.executable, "-m", "pip", "install", module])

# Llamar a la función de instalación de módulos
install_modules()

# Ahora importa los módulos después de haber intentado instalarlos
import requests
import zipfile
import shutil
from datetime import datetime
import tkinter as tk
from tkinter import filedialog, messagebox

# Configuración de la base de datos y la carpeta del proyecto
DB_NAME = 'gimnas'
USER = 'root'
PASSWORD = ''  # Contraseña vacía para el usuario root sin contraseña
XAMPP_PATH = 'C:\\xampp'  # Cambia esto si tu ruta de instalación de XAMPP es diferente
BACKUP_FOLDER = os.path.dirname(os.path.abspath(__file__))  # Carpeta del script
PROJECT_FOLDER = 'C:\\xampp\\htdocs\\gimnas'  # Carpeta donde se descargará el repositorio
GITHUB_REPO_URL = 'https://github.com/usuario/repositorio/archive/refs/heads/main.zip'  # URL del archivo ZIP de GitHub

def create_backup():
    try:
        backup_file = os.path.join(BACKUP_FOLDER, f"{DB_NAME}_backup_{datetime.now().strftime('%Y%m%d_%H%M%S')}.sql")
        command = f'"{XAMPP_PATH}\\mysql\\bin\\mysqldump.exe" -u {USER} {DB_NAME}'
        with open(backup_file, 'w') as f:
            subprocess.run(command, stdout=f, stderr=subprocess.PIPE, shell=True)
        
        messagebox.showinfo("Copia de Seguridad", f"Copia de seguridad creada: {backup_file}")
    except Exception as e:
        messagebox.showerror("Error", f"Error al crear la copia de seguridad: {e}")

def restore_backup():
    try:
        backup_file = filedialog.askopenfilename(
            initialdir=BACKUP_FOLDER,
            title="Selecciona archivo de copia de seguridad",
            filetypes=[("SQL Files", "*.sql")]
        )
        if not backup_file:
            return

        command = f'"{XAMPP_PATH}\\mysql\\bin\\mysql.exe" -u {USER} {DB_NAME}'
        with open(backup_file, 'r') as f:
            subprocess.run(command, stdin=f, stderr=subprocess.PIPE, shell=True)
        
        messagebox.showinfo("Restauración de Base de Datos", f"Base de datos restaurada desde: {backup_file}")
    except Exception as e:
        messagebox.showerror("Error", f"Error al restaurar la base de datos: {e}")

def update_project():
    try:
        # Descargar el archivo ZIP del repositorio de GitHub
        response = requests.get(GITHUB_REPO_URL)
        zip_path = os.path.join(BACKUP_FOLDER, 'update.zip')

        # Guardar el archivo ZIP temporalmente
        with open(zip_path, 'wb') as f:
            f.write(response.content)

        # Extraer el contenido del ZIP en la carpeta del proyecto
        with zipfile.ZipFile(zip_path, 'r') as zip_ref:
            temp_extract_folder = os.path.join(BACKUP_FOLDER, 'temp_update')
            zip_ref.extractall(temp_extract_folder)

        # Copiar los archivos al directorio del proyecto
        extracted_folder = os.path.join(temp_extract_folder, os.listdir(temp_extract_folder)[0])
        for item in os.listdir(extracted_folder):
            source_path = os.path.join(extracted_folder, item)
            target_path = os.path.join(PROJECT_FOLDER, item)
            if os.path.isdir(source_path):
                if os.path.exists(target_path):
                    shutil.rmtree(target_path)
                shutil.copytree(source_path, target_path)
            else:
                shutil.copy2(source_path, target_path)

        # Limpiar archivos temporales
        os.remove(zip_path)
        shutil.rmtree(temp_extract_folder)

        messagebox.showinfo("Actualización", "El proyecto se ha actualizado correctamente desde GitHub.")
    except Exception as e:
        messagebox.showerror("Error", f"Error al actualizar el proyecto: {e}")

# Interfaz gráfica con tkinter
root = tk.Tk()
root.title("Copia de Seguridad MySQL")
root.geometry("400x250")

# Etiquetas y botones
label = tk.Label(root, text="Base de Datos: gimnas", font=("Arial", 14))
label.pack(pady=10)

backup_button = tk.Button(root, text="Crear Copia de Seguridad", command=create_backup, width=25, font=("Arial", 12))
backup_button.pack(pady=5)

restore_button = tk.Button(root, text="Restaurar Copia de Seguridad", command=restore_backup, width=25, font=("Arial", 12))
restore_button.pack(pady=5)

update_button = tk.Button(root, text="Actualizar desde GitHub", command=update_project, width=25, font=("Arial", 12))
update_button.pack(pady=5)

exit_button = tk.Button(root, text="Salir", command=root.quit, width=25, font=("Arial", 12))
exit_button.pack(pady=20)

root.mainloop()
