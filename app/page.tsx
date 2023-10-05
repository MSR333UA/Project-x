import Image from 'next/image'
import styles from './page.module.css'

import type { Metadata } from 'next'
import HeroSection from '@/components/HeroSection'


export const metadata: Metadata = {
  title: 'Create Next App',
  description: 'Generated by create next app',
}

export default function Home() {
  return (
<><HeroSection/></>
  )
}
