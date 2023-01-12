import requests
import yaml
from pprint import pprint

class Reg:

    @staticmethod
    def recurseDocket(docket_id, api_key, page, dir_to_save_to):
        print("Recursing Docket")
        url = f"https://api.regulations.gov/v4/documents?filter[docketId]={docket_id}&page[size]=250&api_key={api_key}"

        print(f"\tRequesting url:\n\t{url}")
        r = requests.get(url)

        docket = r.json()

        if('data' in docket):
            if(len(docket['data']) > 0):
                for document_obj in docket['data']:
                    Reg.recurseComments(document_obj,api_key,1,dir_to_save_to) # start from page 1
            else:
                print("data list is empty in docket")
                pprint(docket)
        else:
            print("No 'data' in docket")
            pprint(docket)

    @staticmethod
    def recurseComments(document_obj, api_key, page, dir_to_save_to):
        
        print(f"Recursing Comment List Page {page}")
        
        pprint(document_obj)

        object_id = document_obj['attributes']['objectId']

        url = f"https://api.regulations.gov/v4/comments?filter[commentOnId]={object_id}&page[size]=250&page[number]={page}&sort=lastModifiedDate,documentId&api_key={api_key}"

        print(f"\tRequesting url:\n\t{url}")

        r = requests.get(url)
        
        comment = r.json()

        if('data' in comment):
            if(len(comment['data']) > 0):
                for comment_obj in comment['data']:
                    pprint(comment_obj)
                    Reg.saveSingleComment(comment_obj['id'],api_key,dir_to_save_to)
            else:
                print("comment data field has no length")
                pprint(comment)
        else:
            print("No data in comment")

        #Now we find out if paging is required. 
        if(comment['meta']['hasNextPage']): 
            #then there is a next page
            page = page + 1
            return(Reg.recurseComments(document_obj, api_key, page, dir_to_save_to))



    @staticmethod
    def saveSingleComment(comment_id,api_key,dir_to_save_to):
        print(f"Getting single comment {comment_id}")

        #note: without include=attachments this adds a whole additional call to the attachments endpoint.
        url = f"https://api.regulations.gov/v4/comments/{comment_id}?include=attachments&api_key={api_key}" 
        print(f"\tRequesting url:\n\t{url}")


        r = requests.get(url)

        with open(f"{dir_to_save_to}/{comment_id}.json", 'w') as f:
            f.write(r.text)

        i = 0
        comment = r.json()
        if 'included' in comment:
            for  inc_obj in comment['included']:
                for this_file_format in inc_obj['attributes']['fileFormats']:
                    print("Looking at a single line of file format")
                    pprint(this_file_format)
                    i = i + 1
                    file_url = this_file_format['fileUrl']
                    file_type = this_file_format['format']


                    response = requests.get(file_url)
                    attach_file = open(f"{dir_to_save_to}{comment_id}.attachment_{i}.{file_type}", 'wb')
                    attach_file.write(response.content)
                    attach_file.close()
                    print("\t\tAttachment ", i, " downloaded")



        
    
    



with open('../regulations.gov.api.yaml', 'r') as yaml_file:
       yamldata = yaml.safe_load(yaml_file)

api_key = yamldata['regulations_gov_api_key']

docket_id = 'CMS-2022-0163' # for testing

Reg.recurseDocket(docket_id,api_key, 1, './data/')


