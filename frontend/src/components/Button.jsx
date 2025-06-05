import { useNavigate } from "react-router-dom";

export default function Button() {
  const navigate = useNavigate();

  return (
    <button
      onClick={() => navigate("/events")}
      className="text-xl sm:text-2xl font-semibold my-5 bg-[#0062FF] hover:bg-[#0051cc] h-14 sm:h-16 w-full sm:w-64 rounded-full transition"
    >
      Explorer nos JPO
    </button>
  );
}
