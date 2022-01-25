/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import React from 'react';

interface ListItemProps {
  id: string;
  name: string;
  description: string;
  checked: boolean;
  callback: ((serviceName:string, newValue:boolean) => void) | null
}

const ListItem = (props:ListItemProps) => {
  const handleCheck = (e:React.ChangeEvent<HTMLInputElement>) => {
    if (props.callback) {
      props.callback(props.id, e.target.checked);
    }
  };
  return (
        <li>
            <h3>{props.name}</h3>
            <p>{props.description}</p>
            <input type="checkbox" defaultChecked={props.checked} disabled={props.callback === null} onChange={(e) => handleCheck(e)} />
        </li>
  );
};

export default ListItem;
