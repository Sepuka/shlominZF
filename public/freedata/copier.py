#! /usr/bin/env python
# -*- coding: utf-8 -*-
# $Id: copier.py 22 2011-01-29 07:12:08Z Sepuka $

import sys
import os
import time
import shutil
import glob

class Copier:
    __logFile   =   'copier.log'        # Файл для логов
    __sleep     =   30                  # Время отслеживания изменений, в секундах
    __matches   =   '*.avi'             # Шаблон файлов
    __logDescr  =   None
    __files     =   {}
    __path      =   None
    __backup    =   None

    def __init__(self, path, backup):
        self.__isPathOK(path, backup)
        self.__initLoger()
        os.chdir(self.__path)
        while 1:
            try:
                self.__checker()
                time.sleep(self.__sleep)
            except (KeyboardInterrupt):
                print 'Bye'
                exit(0)

    def __del__(self):
        if self.__logDescr is not None:
            self.__log('Copier stop')
            self.__logDescr.close()

    def __isPathOK(self, path, backup):
        if os.path.exists(path):
            if os.path.isdir:
                if os.access(path, os.R_OK):
                    self.__path = path
                else:
                    print 'Путь %s не доступен для чтения!' % path
                    exit(1)
            else:
                print 'Путь %s должен быть каталогом!' % path
                exit(1)
        else:
            print 'Путь %s не доступен!' % path
            exit(1)

        if os.path.exists(backup):
            if os.path.isdir(backup):
                if os.access(backup, os.W_OK):
                    self.__backup = backup
                else:
                    print 'Путь %s защищен от записи!' % backup
                    exit(1)
            else:
                print 'Путь %s должен быть каталогом!' % backup
                exit(1)
        else:
            print 'Путь %s не доступен!' % backup
            exit(1)

    def __log(self, msg, *params):
        msg = str(msg) % params
        self.__logDescr.write(time.strftime('%Y-%m-%d %H:%M:%S') + ' ' + msg + '\n')
        self.__logDescr.flush()

    def __initLoger(self):
        self.__logDescr = file(self.__logFile, 'a')
        self.__log('Copier started')

    def __checker(self):
        for file in glob.iglob(self.__matches):
            newSize = os.stat(file).st_size
            try:
                if self.__files[file] != newSize:
                    self.__log('Файл %s изменился на %s', file, self._diffSize(newSize - self.__files[file]))
                    shutil.copy(file, self.__backup)
            except (KeyError):
                pass
            self.__files.setdefault(file, os.stat(file).st_size)

    def _diffSize(self, diff, unit = 0):
        units = ('b', 'Kb', 'Mb', 'Gb', 'Tb')
        if diff > 1024:
            diff = float(diff) / 1024
            unit += 1
        if diff > 1024:
            return self._diffSize(diff, unit)
        else:
            return '%3.2f %s' % (round(diff, 2), units[unit])

if __name__ == '__main__':
    usage = 'usage: %s path_to_file path_to_backup' % sys.argv[0]
    if len(sys.argv) == 3:
        copier = Copier(sys.argv[1], sys.argv[2])
    else:
        print usage
        exit(1)
