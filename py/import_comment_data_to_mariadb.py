#This file assumes all of the comment data lives in the ./data/ directory
#it understands how to import the raw text files from attachment OCR and the JSON file files and the occasional natively occuring RTF file
# This is safe to run again and again because it uses REPLACE INTO 

import sqlalchemy
import yaml
import pandas as pd

with open('../database.yaml', 'r') as yaml_file:
       database = yaml.safe_load(yaml_file)

api_key = ['regulations_gov_api_key']

username = database['username']
password = database['password']
server = database['server']
port = database['port']
unix_socket = database['unix_socket']
db = database['db']

sql_url = f"mysql+pymysql://{username}:{password}@{server}:{port}/{db}"

engine = sqlalchemy.create_engine(sql_url)

#From here on down, we can do anything that Pandas and SQLAlchemy can do
#With data from the MySQL database.

#lets show a few databases from the metadata.. as a test!!
select_sql = f"""
SELECT * FROM geek.comment  
"""

df = pd.read_sql_query(select_sql, engine)
print(df)

