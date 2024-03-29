#This file assumes all of the comment data lives in the ./data/ directory
#it understands how to import the raw text files from attachment OCR and the JSON file files and the occasional natively occuring RTF file
# This is safe to run again and again because it uses REPLACE INTO 

import sqlalchemy
import yaml
import pandas as pd
import glob
import os
import json
from pprint import pprint
import re
from bs4 import BeautifulSoup,  MarkupResemblesLocatorWarning
import warnings

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
conn = engine.connect()

#From here on down, we can do anything that Pandas and SQLAlchemy can do
#With data from the MySQL database.

#We will need two globs (at least) to import all the data.. one for json and one for text..


warnings.filterwarnings("ignore", category=MarkupResemblesLocatorWarning)

#oftimes html sneaks throough into the comments.. we want all of it gone!!
def parse_html(html):
    soup = BeautifulSoup(html,features="html.parser")
    return(soup.get_text())

def simplify_comment_string(comment):
    # do not want any puctuation or special characters. Just words, then make it all lowercase...  
    comment = parse_html(comment)

    comment = re.sub('[^a-zA-Z0-9 \n\.]', '', comment)
    comment = comment.lower()
    comment = ' '.join(comment.split()) #removes the double spaces in the text
    return(comment)

#We always want to use the same code to generate the REPLACE INTO SQL
def make_sql_replace_into(comment_id,comment_source,file_name,comment):
    replace_sql = f"""
REPLACE INTO {db}.{comment_table} 
    (`id`, `comment_identifier`, 
    `comment_source`, `comment_filepath`, `comment_text`) 
VALUES 
    (NULL, '{comment_id}', '{comment_source}', '{file_name}', '{comment}');

"""

    return(replace_sql)
    


### Ok all our helper functions are defined. 
### Basic setup data: 
data_dir = './data/'
db = 'geek'
comment_table = 'comment'


###############################
### JSON processing

file_list = glob.glob(data_dir + '/*.json') # all of the raw comments json

comment_source = 'inline_json'

for file_name in file_list:
    f = open(file_name)
    jdata = json.load(f)

    comment_id = jdata['data']['id']
    
    if 'comment' in jdata['data']['attributes']:
        comment = jdata['data']['attributes']['comment']
        if(comment is not None):
            comment = simplify_comment_string(comment)

            replace_sql = make_sql_replace_into(comment_id,comment_source,file_name,comment)
            #print(replace_sql)
            conn.execute(replace_sql)
        else:
            print(f"comment is None in {file_name}")
    else:
        print(f"no comment in {file_name}")

    print(f"Finished: {file_name}")

print(f"Done with json processing")
###############################
### TXT processing

# Here we cannot interrogarte the JSON data for metadata.. we have metadata in the file name.. or we do not have it. 


file_list = glob.glob(data_dir + '/*.txt') + glob.glob(data_dir + '/*.rtf')

for file_name in file_list:
    just_file_name = os.path.basename(file_name) # just the file name

    file_name_list = just_file_name.split('.')
    comment_id = file_name_list[0]
    comment_source = file_name_list[1] # This will usually be something like 'attachment_1' 
    with open(file_name, 'r') as this_file:
        comment = this_file.read()
        comment = simplify_comment_string(comment)

        replace_sql = make_sql_replace_into(comment_id,comment_source,file_name,comment)

        conn.execute(replace_sql)

    print(f"Finished: {file_name}")

