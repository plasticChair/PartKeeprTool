#Deadbug Electronics

import requests
import re
from bs4 import BeautifulSoup
import time
from selenium import webdriver
import pickle

openDriver = False
partOK     = False
DBCat      = ""

def mouserMiner(part, driver):

    #Output data struct
    newDataStruct = {}
    ProductURL = None
    url = 'https://api.mouser.com/api/v1/search/partnumber?apiKey=51594582-af9b-45be-956a-e41ccd8a8a6a'
    payload = "{ \"SearchByPartRequest\": { \"mouserPartNumber\": \" " + part + "\", \"partSearchOptions\": \"string\" }}"
    headers = {'content-type': 'application/json', 'accept': 'application/json'}
    r = requests.post(url, data=payload, headers=headers)


    temp = r.json()
    #Grab first result
    if ( temp['SearchResults']['NumberOfResult'] != 0):
        ProductURL = temp['SearchResults']['Parts'][0]['ProductDetailUrl']
        ProductDisc = temp['SearchResults']['Parts'][0]['Description']
        ProductCat = temp['SearchResults']['Parts'][0]['Category']
        ProductMan = temp['SearchResults']['Parts'][0]['Manufacturer']
        ProductImage = temp['SearchResults']['Parts'][0]['ImagePath']
        DataSheetUrl = temp['SearchResults']['Parts'][0]['DataSheetUrl']

        if(ProductImage == None):
            ProductImage = ""

        if (DataSheetUrl == None):
            DataSheetUrl = ""

        if (ProductCat == None):
            ProductCat = ""


        detaSet1 = {}
        detaSet1['ProductDetailUrl'] = ProductURL
        detaSet1['Description']      = ProductDisc
        detaSet1['Category']         = ProductCat
        detaSet1['Manufacturer']     = ProductMan
        detaSet1['ImagePath']        = ProductImage
        detaSet1['DataSheetUrl']     = DataSheetUrl


        ##############################
        # Start Data mining
        ##############################
        #This part is tricky because mouser detects robots.  First try to get the page
        driver.get(ProductURL)
        html = driver.page_source

        first = 0
        strFind = "<strong>Pardon Our Interruption</strong>"
        while (True):
            #Check to see if mouser caught you
            if strFind in html:
                if first == 0:
                    print("     Found robo search")
                    try: #If you have cookies, load them
                        pass
                        cookies = pickle.load(open("cookies.pkl", "rb"))
                        for cookie in cookies:
                            driver.add_cookie(cookie)
                    except:
                        pass
                    #Reload the page. If cookies loaded or another window is open, Captcha can be bypassed, sometimes...
                    driver.get(ProductURL)
                    time.sleep(6)
                    first = 1
                else:
                    #Need some sleepy time between requests, otherwise mouser will block you
                    time.sleep(6)
                html = driver.page_source

            else:
                #print("escaped")
                break

        soup = BeautifulSoup(html, 'html.parser')

        # return tag results
        results = soup.findAll('div', class_='div-table-row')
        detaSet2 = {}
        if results != []:
            for item in results:
                TableTitle = []
                TableValue = []
                #Loop through app the part params.
                try:
                    itemInfo = item.find_all(attrs={"class" : "col-xs-4"})
                    itemInfo = str(itemInfo[0]).replace('\n', ' ').replace('\r', '')

                    TableTitle = re.search('name=\"SpecList\[\d{1,2}\].Label\" type=\"hidden\" value=\"(.*)\"\/> <\/label>', itemInfo).group(1)
                    itemInfo = item.find_all(attrs={"class" : "col-xs-5"})
                    itemInfo = str(itemInfo[0]).replace('\n', ' ').replace('\r', '')
                    TableValue = re.search('name=\"SpecList\[\d{1,2}\].Value\" type=\"hidden\" value=\"(.*)\"\/><input', itemInfo).group(1)

                    if (TableValue !="-"):
                        detaSet2[TableTitle] = TableValue
                except:
                    pass

            detaSet1['PartParameters'] = detaSet2
            newDataStruct.update({part: detaSet1})
            return newDataStruct
    return None




