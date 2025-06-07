import HeroButton from "./HeroButton";

export function CallToAction() {
  return (
    <div className="flex justify-center items-center mt-16 mb-20">
      <div className="h-44 w-[50%] bg-[#0062FF] flex flex-col justify-center items-center rounded-xl gap-5">
        <p className="font-Poppins text-lg font-medium">
          Envie d’en voir plus ? Nos prochains événements n’attendent que vous.</p>
        <HeroButton
          to="/events"
          className="bg-white text-[#0062FD] hover:bg-gray-300 !w-72" 
        />
      </div>
    </div>
  )
}
