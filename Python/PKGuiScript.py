#Deadbug Electronics


from PyQt5 import QtCore, QtGui, QtWidgets

from PyQt5.QtCore import *
from selenium import webdriver
import webbrowser
from PKCmdCntrl import *
import pyperclip

import time

class saveMem:
    def __init__(self):
        self.url = ""
        self.drive = None
        self.part  = None

## Class for threading
class updatePkThread(QThread):
    def __init__(self, ui):
        QThread.__init__(self)
        self.UI = ui

    def __del__(self):
        self.wait()

    def run(self):
        addPartsPK(self.UI)

## Class to direct print to text box
class Stream(QtCore.QObject):
    newText = QtCore.pyqtSignal(str)

    def write(self, text):
        self.newText.emit(str(text))

# Find all empty parts (missing PN)
def findEmptyPK ():
    results = findEmpty()
    print("--------- Empties-----------")
    for item in results:
        print(item)

# Add a new part not in OK
def createPart():
    print("--------- Make PN -----------")
    for line in ui.partsList.toPlainText().splitlines():
        if line != "":
            createParts(line)

# Add/update parts
def addPartsPK (ui):
    URLList = []
    print("--------- Updating Parts -----------")
    sleepTime = 6
    t0 = time.time()
    for line in ui.partsList.toPlainText().splitlines():
        if line != "":
            partSelected = []
            partSelected.append(line)
            ui.memSave.part = partSelected
            urlOut = setPart2PK(ui.memSave, ui.driver)
            try:
                URLList.append(urlOut[0])
            except:
                t0 = t0 - 6
                pass
            # NOTE! Need to wait a few seconds between requests.  Otherwise mouser will block you
            while (True):
                if ((time.time() - t0) > sleepTime):
                    t0 = time.time()
                    break

    try:
        ui.memSave.drive.close()
    except:
        pass
    ui.URLList = URLList
    print("     DONE")
    print("")

# Add or remove stock
def modStockPK(ui,dir):
    if (dir == 1):
        print("--------- Adding -------------")
    else:
        print("--------- Removing -----------")
    for line in ui.invList.toPlainText().splitlines():
         modStock(line, dir)


class Ui_MainWindow(object):
    def setupUi(self, MainWindow):
        MainWindow.setObjectName("MainWindow")
        MainWindow.resize(412, 695)
        self.centralwidget = QtWidgets.QWidget(MainWindow)
        self.centralwidget.setObjectName("centralwidget")
        self.invList = QtWidgets.QTextEdit(self.centralwidget)
        self.invList.setGeometry(QtCore.QRect(20, 50, 171, 281))
        self.invList.setObjectName("invList")
        self.addInv = QtWidgets.QPushButton(self.centralwidget)
        self.addInv.setGeometry(QtCore.QRect(50, 370, 111, 23))
        self.addInv.setObjectName("addInv")
        self.remInv = QtWidgets.QPushButton(self.centralwidget)
        self.remInv.setGeometry(QtCore.QRect(50, 340, 111, 31))
        self.remInv.setObjectName("remInv")
        self.statusDisplay = QtWidgets.QTextBrowser(self.centralwidget)
        self.statusDisplay.setGeometry(QtCore.QRect(25, 410, 350, 250))
        self.statusDisplay.setObjectName("statusDisplay")
        self.partsList = QtWidgets.QTextEdit(self.centralwidget)
        self.partsList.setGeometry(QtCore.QRect(220, 50, 171, 281))
        self.partsList.setObjectName("partsList")
        self.addParts = QtWidgets.QPushButton(self.centralwidget)
        self.addParts.setGeometry(QtCore.QRect(240, 340, 121, 31))
        self.addParts.setObjectName("addParts")
        self.icon = QtWidgets.QLabel(self.centralwidget)
        self.icon.setGeometry(QtCore.QRect(330, 0, 61, 41))
        self.icon.setText("")
        self.icon.setPixmap(QtGui.QPixmap("Logo.bmp"))
        self.icon.setScaledContents(True)
        self.icon.setObjectName("icon")
        self.title = QtWidgets.QLabel(self.centralwidget)
        self.title.setGeometry(QtCore.QRect(60, 0, 261, 41))
        self.title.setTextFormat(QtCore.Qt.RichText)
        self.title.setAlignment(QtCore.Qt.AlignCenter)
        self.title.setOpenExternalLinks(True)
        self.title.setTextInteractionFlags(QtCore.Qt.LinksAccessibleByKeyboard | QtCore.Qt.LinksAccessibleByMouse)
        self.title.setObjectName("title")
        self.openLink = QtWidgets.QPushButton(self.centralwidget)
        self.openLink.setGeometry(QtCore.QRect(20, 10, 31, 23))
        self.openLink.setObjectName("openLink")
        self.findEmpty = QtWidgets.QPushButton(self.centralwidget)
        self.findEmpty.setGeometry(QtCore.QRect(300, 370, 61, 23))
        self.findEmpty.setObjectName("findEmpty")
        self.getURLs = QtWidgets.QPushButton(self.centralwidget)
        self.getURLs.setGeometry(QtCore.QRect(370, 350, 31, 21))
        self.WS = QtWidgets.QPushButton(self.centralwidget)
        self.WS.setGeometry(QtCore.QRect(370, 370, 31, 21))
        font = QtGui.QFont()
        font.setPointSize(7)
        self.WS.setFont(font)
        self.WS.setObjectName("WS")
        self.getURLs.setFont(font)
        self.getURLs.setObjectName("getURLs")
        self.createParts = QtWidgets.QPushButton(self.centralwidget)
        self.createParts.setGeometry(QtCore.QRect(240, 370, 61, 23))
        font = QtGui.QFont()
        font.setPointSize(7)
        self.createParts.setFont(font)
        self.createParts.setObjectName("createParts")
        MainWindow.setCentralWidget(self.centralwidget)
        self.statusbar = QtWidgets.QStatusBar(MainWindow)
        self.statusbar.setObjectName("statusbar")
        MainWindow.setStatusBar(self.statusbar)
        self.retranslateUi(MainWindow)
        QtCore.QMetaObject.connectSlotsByName(MainWindow)

        #Setup struct
        self.memSave = saveMem()

        #Setup output
        sys.stdout = Stream(newText=self.onUpdateText)

        ## Setup buttons
        self.addInv.clicked.connect(lambda: modStockPK(self,1))
        self.remInv.clicked.connect(lambda: modStockPK(self, -1))
        self.addParts.clicked.connect(self.addPartsFun)
        self.WS.clicked.connect(self.openWeb)
        self.openLink.clicked.connect(lambda: self.openPK())
        self.getURLs.clicked.connect(lambda: self.copyURLs())
        self.createParts.clicked.connect(lambda: createPart())
        self.findEmpty.clicked.connect(lambda: findEmptyPK())

        # Setup output
        self.URLList =[]

        #Setup threading
        self.get_thread = updatePkThread(self)

        # Mark if selenium is open
        self.openedWeb = 0
        logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.DEBUG)

    def openWeb(self):
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/1.0.154.36 Safari/525.19',
            "Upgrade-Insecure-Requests": "1", "DNT": "1",
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.9",
            "Accept-Language": "en-US,en;q=0.9", "Accept-Encoding": "gzip, deflate, br"}

        options = webdriver.ChromeOptions()
        options.binary_location = "C:/Program Files (x86)/Google/Chrome/Application/chrome.exe"
        driver_path = "chromedriver.exe"
        options.add_argument('--disable-extensions')
        options.add_argument('--profile-directory=Default')
        options.add_argument("disable-infobars")
        # options.add_argument("--incognito")
        options.add_argument("--disable-plugins-discovery");
        options.add_argument("--start-maximized")
        options.add_argument(f'user-agent=headers')
        options.add_argument("--enable-javascript")



        self.driver = webdriver.Chrome(options=options, executable_path=driver_path)
        #  try:
        #      load_cookie(driver, '/cookie')
        #     except:
        #        pass

        self.driver.header_overrides = {
            'content-encoding': 'gzip'
        }
        # driver.delete_all_cookies()
        self.driver.set_window_size(800, 800)
        self.driver.set_window_position(0, 0)
        self.openedWeb = 1

    def addPartsFun(self):
        if self.openedWeb == 0:
            self.openWeb()
        self.get_thread.start()

    def openPK(self):
        webbrowser.open_new_tab('http://.192.168.1.141')

    def copyURLs(self):
        pyperclip.copy("\n".join(self.URLList))

    def onUpdateText(self, text):
        #ui.statusDisplay.append(text.strip())
        cursor = self.statusDisplay.textCursor()
        cursor.movePosition(QtGui.QTextCursor.End)
        cursor.insertText(text)
        self.statusDisplay.setTextCursor(cursor)
        self.statusDisplay.ensureCursorVisible()

    def __del__(self):
        sys.stdout = sys.__stdout__

    def retranslateUi(self, MainWindow):
        _translate = QtCore.QCoreApplication.translate
        MainWindow.setWindowTitle(_translate("MainWindow", "MainWindow"))
        self.addInv.setText(_translate("MainWindow", "Add Inventory"))
        self.remInv.setText(_translate("MainWindow", "Remove Inventory"))
        self.addParts.setText(_translate("MainWindow", "Add Parts"))
        self.title.setText(_translate("MainWindow","<html><head/><body><p><span style=\" font-size:16pt;\">PartKeepr Custom Tool</span></p></body></html>"))
        self.openLink.setText(_translate("MainWindow", "PK"))
        self.findEmpty.setText(_translate("MainWindow", "Find Empty"))
        self.getURLs.setText(_translate("MainWindow", "URLs"))
        self.createParts.setText(_translate("MainWindow", "Create Parts"))
        self.WS.setText(_translate("MainWindow", "WEB"))

if __name__ == "__main__":
    import sys
    import logging
    logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.DEBUG)

    app = QtWidgets.QApplication(sys.argv)
    MainWindow = QtWidgets.QMainWindow()
    ui = Ui_MainWindow()
    ui.setupUi(MainWindow)
    MainWindow.show()
    sys.exit(app.exec_())
