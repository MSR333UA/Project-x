"use client"

import * as React from 'react'
import { ChakraProvider } from '@chakra-ui/react'
import { CacheProvider } from '@chakra-ui/next-js'


// These styles apply to every route in the application
import './globals.css'


import { Inter } from 'next/font/google'
import Navbar from '@/components/Navbar'


const inter = Inter({ subsets: ['latin'] })



export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return ( 
    <html lang="en">
    <body > 
    <CacheProvider>
      <ChakraProvider>
        <Navbar/>
        {children}
      </ChakraProvider>
    </CacheProvider>
    </body>
  </html>
  )
}
