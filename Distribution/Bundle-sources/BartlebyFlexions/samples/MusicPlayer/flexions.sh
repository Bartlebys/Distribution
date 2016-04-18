#!/bin/sh 

# Created by Benoit Pereira da Silva on 20/04/2013.
# Copyright (c) 2013  http://www.chaosmos.fr
#
# This file is part of Flexions
#
# Flexions is free software: you can redistribute it and/or modify
# it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Flexions is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU LESSER GENERAL PUBLIC LICENSE for more details.
#
# You should have received a copy of the GNU LESSER GENERAL PUBLIC LICENSE
# along with Flexions  If not, see <http://www.gnu.org/license


########################
# Configuration  
########################

# We define the  path to the Flexions root folder.
flexionsFolder="../../"

. ${flexionsFolder}default.flx

#You can override the default variables
# templates, pre , post, destination

# We setup the descriptor  
descriptor="datasources/MusicPlayer.xcdatamodel/contents"

#You can specify a destination folder
#If not it will  generate in the out/ folder
destination="out.flexions/"

###############
# Invoke flexions 
###############

. ${flexionsFolder}flexions.flx
