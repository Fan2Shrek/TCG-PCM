export type TextInputProps = {
  label: string;
  onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
  type?: string;
};

export default ({ label, onChange = null, type = 'text'}: TextInputProps) => (
  <div className="flex flex-col gap-4">
	<label htmlFor="username" className="text-sm font-medium text-gray-700">
	  { label }
	</label>
	<input
	  type={type}
	  id={label}
	  name={label}
	  className="border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
	  placeholder="Enter your username"
	  onChange={onChange}
	/>
  </div>
);
