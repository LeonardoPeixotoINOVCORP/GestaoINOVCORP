# Clonar o repositório
git clone https://github.com/LeonardoPeixotoINOVCORP/GestaoINOVCORP.git  
cd biblioteca

# Instalar dependências do Laravel
composer install

# Instalar dependências do frontend
npm install

# Configurar o ambiente
cp .env.example .env  
php artisan key:generate

# Lembre-se de configurar os dados da base de dados no arquivo .env

# Criar as tabelas da base de dados
php artisan migrate

# Inserir dados iniciais
php artisan db:seed

# Executar o frontend
npm run dev
