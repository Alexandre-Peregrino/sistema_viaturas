import requests
import base64
import hashlib
import socket
import pandas as pd
from datetime import datetime
import os

from dotenv import load_dotenv
load_dotenv()


class Sisgp:
    __dev = None
    __headers = None
    __cpf = None
    __dev = 'teste'
    __dados_producao = {'servidor': 'https://www3.pm.rn.gov.br', 'sistema': 'ROTAWEB', 'semente': '4rtppCjGQLc@5iKN757qJKRu@gLdzd'}
    __dados_teste = {'servidor': 'https://www5.defesasocial.rn.gov.br', 'sistema': 'ROTAWEB', 'semente': '4rtppCjGQLc@5iKN757qJKRu@gLdzd'}
    __dados = None
    
    def __init__(self):
        self.__dev = os.getenv('MODE')
        if self.__dev == 'producao':
            print('\tSISGP no modo PRODUÇÃO!!!')
            self.__dados = self.__dados_producao
        else:
            print('\tSISGP no modo TESTE!!!')
            self.__dados = self.__dados_teste
            

        self.__base_url = f"{self.__dados['servidor']}/sisgpws/api/v2/rotaweb"
        self.__cpf = os.getenv('SISGP_USER')
        self.__headers = {
            'Accept': 'application/json',
            'Token': self.get_token() 
            }

    def get_token(self):
        ip = socket.gethostbyname(socket.gethostname()) 
        sistema = self.__dados['sistema']
        textoEncode = base64.b64encode(f'{sistema}@{ip}@{self.__cpf}'.encode("utf-8")).decode("utf-8")
        
        data = datetime.now().strftime('%Y%m%d')
        semente = self.__dados['semente']
        textoCifrado = hashlib.md5(f'{data}{sistema}{textoEncode}{semente}'.encode()).hexdigest()

        return f'{textoCifrado}@{textoEncode}'

    def getPoliciais(self):
        try:
            response = requests.get(f'{self.__base_url}/policiais', headers= self.__headers)
            response.raise_for_status() 
            if(len(response.json()) > 0):
                df = pd.DataFrame(response.json())
                return df
        except requests.exceptions.RequestException as e:
            print(f'Erro ao obter policial: {e}')
            return pd.DataFrame()
        