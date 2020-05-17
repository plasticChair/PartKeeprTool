# Deadbug Electronics

import urllib.request
import json
import requests
from MouserMine import mouserMiner
import os.path
import csv
import time

#############################################
url = "http://192.168.1.141/User/rest.php"
#############################################


VARIABLE = "Var"
CONTROL = "Control"
ID = "ID"
DATA = "Data"
urlList = []


class saveMem:
    def __init__(self):
        self.url = ""
        self.drive = None
        self.part = None


def findEmpty():
    # Get empty Part
    empties = getRequest("FindEmpty")
    return empties


def genQRCode(PN):
    encoding = 'utf-8'
    urllib.request.urlretrieve(
        "https://barcode.tec-it.com/barcode.ashx?data=" + str(PN) + "&code=MicroQR&dpi=96&dataseparator=",
        "images\\QR\\QR_" + str(PN) + ".jpeg")


def getRequest(commandIn, varIn=None, dataIn=None):
    global url, CONTROL, PART

    PARAMS = {CONTROL: commandIn,
              VARIABLE: varIn,
              DATA: dataIn}

    r = requests.request("GET", url, params=PARAMS)
    try:
        y = json.loads(r.text)
        return y["result"]
    except:
        print(r.text)
        print("PHP error")
        return None


def modStock(part, dir):
    result = getRequest("Stock", dir, part)
    print(result + " -> " + part)


def createParts(part):
    result = getRequest("MakePart", part)
    if result == None:
        result = "Alreadu Exists"
    print(result + " -> " + part)


def setPart2PK(dataIn, driver):
    urlList = []
    partIN = dataIn.part
    for onePart in partIN:
        print("***************************")
        print(onePart)
        print("***************************")

        # Step 1, Find Part and output internal PN
        ID = getRequest("FindID", onePart)
        if ID == None:
            print("Error, part doesn't exist")
            break
        print("     ID OK-> " + str(ID))

        # Step 2 generate PN
        # input internal PN
        DB_PN = getRequest("GenPN", ID)
        if DB_PN == None:
            print("Error, part number gen -> " + str(DB_PN))
            break

        print("     DB PN OK-> " + str(DB_PN))

        # Step 3, Mine the data
        data = mouserMiner(onePart, driver)
        if data == None:
            print("Error, Part Not Found")
            break

      # data = {'TC4S30F': {'ProductDetailUrl': 'https://www.mouser.com/ProductDetail/Toshiba/TC4S30FT5LT?qs=f0GatGUIxV37iKY3MPBf2w%3D%3D', 'Description': 'Logic Gates x31 Pb EX-OR(4030B)', 'Category': 'Logic Gates', 'Manufacturer': 'Toshiba', 'ImagePath': None, 'DataSheetUrl': '', 'PartParameters': {'Manufacturer:': 'Toshiba', 'Product Category:': 'Logic Gates', 'Product:': 'Single-Function Gate', 'Logic Function:': 'XOR', 'Logic Family:': '4000', 'Number of Gates:': '1 Gate', 'Number of Input Lines:': '2 Input', 'Number of Output Lines:': '1 Output', 'High Level Output Current:': '- 4 mA', 'Low Level Output Current:': '4 mA', 'Propagation Delay Time:': '280 ns', 'Supply Voltage - Max:': '18 V', 'Supply Voltage - Min:': '3 V', 'Minimum Operating Temperature:': '- 40 C', 'Maximum Operating Temperature:': '+ 85 C', 'Mounting Style:': 'SMD/SMT', 'Package / Case:': 'SSOP-5', 'Packaging:': 'Reel', 'Function:': 'XOR', 'Height:': '1.1 mm', 'Length:': '2.9 mm', 'Quiescent Current:': '2 nA', 'Series:': 'TC4S30', 'Width:': '1.6 mm', 'Brand:': 'Toshiba', 'Logic Type:': 'CMOS', 'Operating Supply Voltage:': '3.3 V, 5 V, 9 V, 12 V, 15 V', 'Product Type:': 'Logic Gates', 'Subcategory:': 'Logic ICs'}}}
        dataBack = getRequest("updatePart", ID, str(data))
        if dataBack != "OK":
            print(dataBack)
            print("Error, Update part")
            break

        print("     Part Update OK")

        # Step 4, make URL List
        try:
            urlList.append(data[onePart]['ImagePath'])
            print("     URL updated")
        except:
            print("     No URL")
            pass

        # step 5, print QR
        genQRCode(DB_PN)
        print("     QR Generated")

        # Step 6 generate excel sheet
        try:
            package = data[onePart]["PartParameters"]["Package / Case:"]
        except:
            try:
                package = data[onePart]["PartParameters"]["Case Code - in:"]
            except:
                package = None

        fileExist = os.path.exists('PartsBoxLabelAdd.csv')
        with open('PartsBoxLabelAdd.csv', 'a+', newline='') as csvfile:
            fieldnames = ['Name', 'TL', 'TR', 'BL', 'BR', 'DBPN']
            writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
            if (not fileExist):
                writer.writeheader()
            writer.writerow({'Name': onePart, 'TL': '', 'TR': '', 'BL': package, 'BR': '', 'DBPN': DB_PN})

            print("     Write to File OK")

        # Step 7, return URLs
        return urlList


if __name__ == '__main__':

    memSave = saveMem()
    memSave.part = ['TC33X-2-204ECT-ND']
    setPart2PK(memSave)
    time.sleep(8)
    print("sleepies")
    memSave.part = ['CRCW040240R2FKEDC']
    setPart2PK(memSave)
    time.sleep(8)
    print("sleepies")
    memSave.part = ['TC33X-2-204ECT-ND']
    setPart2PK(memSave)

    memSave.drive.close()
    #Remove Stock
    # modStock("CRCW040240R2FKEDC", -1)

    # Add Stock
    # modStock("CRCW040240R2FKEDC", 1)

    findEmpty()

    print("Done")
    # print(urlList)
