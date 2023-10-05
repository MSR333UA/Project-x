import React from "react";
import { BiPound } from "react-icons/bi";
import {
  FaRegThumbsUp,
  FaClipboardList,
  FaUsersCog,
  FaCheck,
  FaHeadset,
} from "react-icons/fa";
import { FiCheckSquare } from "react-icons/fi";

interface Props {
  children: React.ReactNode;
  icon?: React.ReactNode;
}
const data = [
  {
    text: "One of the most affordable contractors in the UK",
    icon: <BiPound />,
  },
  { text: "Positive feedback from previous clients", icon: <FaRegThumbsUp /> },
];
const Lists = [
  "One of the most affordable contractors in the UK",
  "Positive feedback from previous clients",
  "Available client references for your peace of mind",
  "We work to client`s deadlines and budgets strictly",
  "Every project is quality supervised",
  "Government approved credentials",
  "Live Chat support to give you 24/7 live help",
];

const NavLists = (props: Props) => {
  const { children, icon } = props;
  return (
    <li className="mb-3">
      <span className="flex items-center">
        {icon}
        {children}
      </span>
    </li>
  );
};

const HeroSection: React.FC = () => {
  return (
    <div className="relative w-full h-screen ">
      <video
        className="object-cover w-full h-[400px] "
        src="../assets/video_Sales.mp4"
        autoPlay
        loop
        muted
      />

      <div className="absolute top-[75px] left-20  flex flex-col justify-center items-center ">
        <h1 className="text-3xl mb-5 font-semibold text-[#FAFAFB]">
          Why Choose British Builders?
        </h1>
        <ul className="text-[#FAFAFB]">
          {data.map((item, index) => (
            <NavLists
              key={index}
              icon={item.icon}
              // icon={Icons.map((icon) => (
              //   <Icons key={icon} color='white' size='24px' />
              // ))}
            >
              {item.text}
            </NavLists>
          ))}

          {/* <li className='mb-3'>
    <span className="flex items-center">
      <BiPound color="white" size='24px' /> One of the most affordable contractors in the UK
    </span>
    </li>
    <span className="flex items-center">
      <FaRegThumbsUp color="white" size='24px' /> Positive feedback from previous clients
    </span>
    <li>
    <span className="flex items-center">
      <FiCheckSquare color="white" size='24px' /> Available client references for your peace of mind
    </span>
</li>
    <li>
    <span className="flex items-center">
      <FaClipboardList color="white" size='24px' /> We work to client's deadlines and budgets strictly
    </span>
      </li>
    <li>
    <span className="flex items-center">
      <FaUsersCog color="white" size='24px' /> Every project is quality supervised
    </span>
      </li>
    <li>
    <span className="flex items-center">
      <FaCheck color="white" size='24px' /> Government approved credentials
    </span>
    </li>
    <li>
    <span className="flex items-center">
      <FaHeadset color="white" size='24px' /> Live Chat support to give you 24/7 live help
    </span></li> */}
        </ul>
      </div>
      {/* <iframe width="100%" height="430" 
       src="https://www.youtube.com/embed/M7OcWqyFTFA?autoplay=1&loop=1&controls=0&mute=1&start=4"
      title="YouTube video player" frameBorder="0" 
      allow="accelerometer; autoplay; clipboard-write; 
      encrypted-media; gyroscope; picture-in-picture; web-share" 
      allowFullScreen></iframe> */}
    </div>
  );
};

export default HeroSection;
